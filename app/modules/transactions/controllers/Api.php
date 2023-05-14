<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends public_Controller{

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

    public function online_payment(){
        $file = file_get_contents('php://input');
        if($file){
            file_put_contents("logs/daraja_stk_payment_callback.dat",date("d-M-Y h:i A")."\t".$file."\n",FILE_APPEND);
            header('Content-Type: application/json');
            $result = json_decode($file);
            if($result){
                $data_body = isset($result->Body)?$result->Body:'';
                if($data_body){
                    $callback = $data_body->stkCallback;
                    if($callback){
                        $merchant_request_id = $callback->MerchantRequestID;
                        $CheckoutRequestID = $callback->CheckoutRequestID;
                        if($request = $this->safaricom_m->get_stk_request_by_merchant_request_id_and_checkout_request_id($CheckoutRequestID,$merchant_request_id)){
                            $result_code = trim($callback->ResultCode);
                            $result_description = trim($callback->ResultDesc);
                            $amount = '';
                            $phone= '';
                            $transaction_id= '';
                            $balance= '';
                            $transaction_date= '';
                            if($result_code == '0'){
                                $callback_metadatas = $callback->CallbackMetadata;
                                if($callback_metadatas){        
                                    for ($i=0; $i < 4; $i++) { 
                                        $value_data = $callback_metadatas->Item[$i];
                                        $name = isset($value_data->Name)?$value_data->Name:'';
                                        $value = isset($value_data->Value)?$value_data->Value:'';
                                        if(preg_match('/Amount/', $name)){
                                            $amount = trim($value);
                                        }elseif (preg_match('/PhoneNumber/', $name)) {
                                            $phone = trim($value);
                                        }elseif (preg_match('/MpesaReceiptNumber/', $name)) {
                                            $transaction_id = trim($value);
                                        }elseif (preg_match('/Balance/', $name)) {
                                            $balance = trim($value);
                                        }elseif (preg_match('/TransactionDate/', $name)) {
                                            $transaction_date = strtotime(trim($value))?:time();
                                        }
                                    }
                                }
                            }
                            $update = array(
                                'result_code' => $result_code,
                                'result_description' => $result_description,
                                'transaction_id' => $transaction_id,
                                'organization_balance' => $balance,
                                'transaction_date' => $transaction_date,
                                'modified_on' => time(),
                            );
                            if($this->safaricom_m->update_stkpushrequest($request->id,$update)){                               
                                $request = $this->safaricom_m->get_stk_request($request->id);                                                           
                                if($result_code == '0'){
                                    if($this->transactions->record_transaction($request)){
                                        $response = array(
                                            "ResultDesc" => "successful: ".$transaction_id,
                                            "ResultCode" => "0"
                                        );
                                    }else{
                                        $response = array(
                                            "ResultDesc" => "Failed reconcillation",
                                            "ResultCode" => "1"
                                        );
                                    }
                                }else{
                                	/*phone*/
                                	$user = $this->users_m->get_user_by_phone_number($request->phone);
                                	$notification_array[] = array(
			                            'subject'=>'Transaction',
			                            'message'=>$callback->ResultDesc,
			                            'from_user'=>$user->id,
			                            'to_user_id'=>$user->id,
			                        );                                              
			                        $this->notification_manager->create_bulk($notification_array);
			                        $response = array(
                                        "ResultDesc" => $callback->ResultDesc,
                                        "ResultCode" => "1"
                                    );
                                }
                                //$this->transactions->send_customer_callback($request);
                            }else{
                                $response = array(
                                    "ResultDesc" => "Could not update payment",
                                    "ResultCode" => "1"
                                );
                            }
                        }else{
                            $response = array(
                                "ResultDesc" => "No Initial request",
                                "ResultCode" => "1"
                            );
                        }
                    }else{
                        $response = array(
                            "ResultDesc" => "Empty Callback",
                            "ResultCode" => "1"
                        );
                    }
                }else{
                    $response = array(
                        "ResultDesc" => "Empty Body",
                        "ResultCode" => "1"
                    );
                }
            }else{
                $response = array(
                    "ResultDesc" => "Result file sent : file format error",
                    "ResultCode" => "1"
                );
            }
        }else{
            $response = array(
                "ResultDesc" => "No File",
                "ResultCode" => "1"
            );
        }
        echo json_encode($response);
    }
}