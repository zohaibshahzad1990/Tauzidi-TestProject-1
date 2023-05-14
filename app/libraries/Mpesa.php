<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Mpesa{

	protected $ci;
	public $settings;

  	public function __construct(){
		$this->ci= & get_instance();		
    	$this->ci->load->model('sms/sms_m');
    	$this->ci->load->model('emails/emails_m');
    	$this->ci->load->model('safaricom/safaricom_m');
    	$this->ci->load->config('mpesa', TRUE);
    	$this->mpesa = $this->ci->config->item('mpesa');
        $this->config = $this->ci->config->item('email');
		$this->settings = $this->ci->settings_m->get_settings(1)?:'';
  	}

  	public function initiate_online_checkout($payment_particulars = array()){
  		if($payment_particulars){
  			$payment_object  = (object)$payment_particulars;
			//$shortcode = "174379";
			$shortcode = "4026407";
			$url = $this->mpesa['live_url'] . 'stkpush/v1/processrequest';
        	$timestamp = date("Ymdhis");
        	$password = base64_encode($shortcode . $this->mpesa['lipa_na_live_mpesa_passkey'] . $timestamp);
	        /*$particulars = array(
                'BusinessShortCode' => $shortcode,
            	'Password' => $password,
            	'Timestamp' => $timestamp,
            	'TransactionType' => $simulation_object->TransactionType,
            	'Amount' => $simulation_object->Amount,
            	'PartyA' => $simulation_object->PartyA,
            	'PartyB' => $simulation_object->PartyB,
            	'PhoneNumber' => $simulation_object->PhoneNumber,
            	'CallBackURL' => $simulation_object->CallBackURL,
            	'AccountReference' => $simulation_object->AccountReference,
            	'TransactionDesc' => $simulation_object->TransactionType,
            	'Remark' => $simulation_object->TransactionDesc,
            );*/

	        $request_id = $this->ci->safaricom_m->generate_stkpush_request_id();
	        $input = array(
                'shortcode' => $shortcode,
                'request_id' => $request_id,
                'invoice_id' => $payment_object->invoice_id,
                'amount' => $payment_object->amount,
                'phone' => $payment_object->phone,
                'request_callback_url' => $payment_object->callback_url,
                'reference_number' => $payment_object->reference_number,
                'created_on' => time(),
	        );
	        if($id = $this->ci->safaricom_m->insert_stk_push_request($input)){
	        	//$phone_number = str_replace("+","",valid_phone($phone_number));
	        	$post_data = json_encode(array(
					"BusinessShortCode" => $shortcode,
					"Password" => $password,
					"Timestamp" => $timestamp,
					"TransactionType" => "CustomerPayBillOnline",
					"Amount" => $payment_object->amount,
					"PartyA" => $payment_object->phone,
					"PartyB" => $shortcode,
					"PhoneNumber" => $payment_object->phone,
					"CallBackURL" => "https://api.educationke.com/api/v1/transactions/online_payment",
					"AccountReference" =>  $request_id,
					"TransactionDesc" => "online payment"
				));
				$particulars = array(
	                'BusinessShortCode' => $shortcode,
	            	'Password' => $password,
	            	'Timestamp' => $timestamp,
	            	'TransactionType' => "CustomerPayBillOnline",
	            	'Amount' => $payment_object->amount,
	            	'PartyA' => $payment_object->phone,
	            	'PartyB' => $shortcode,
	            	'PhoneNumber' => $payment_object->phone,
	            	'CallBackURL' => "https://api.educationke.com/api/v1/transactions/online_payment",
	            	'AccountReference' => $request_id,
	            	'TransactionDesc' => "online payment",
	            	'Remark' => "Subscription payment",
	            );
				$response = $this->ci->curl->post_json_checkout_payment($particulars,$url);
				if($response){
					if($res = json_decode($response)){
						$checkout_request_id = isset($res->CheckoutRequestID)?$res->CheckoutRequestID:'';
						$merchant_request_id = isset($res->MerchantRequestID)?$res->MerchantRequestID:'';
						$response_code = isset($res->ResponseCode)?$res->ResponseCode:'';
						$response_description = isset($res->ResponseDescription)?$res->ResponseDescription:'';
						$customer_message = isset($res->CustomerMessage)?$res->CustomerMessage:'';
						$error_code =  isset($res->errorCode)?$res->errorCode:'';
						$error_message =  isset($res->errorMessage)?$res->errorMessage:'';
						if($error_code){
							$this->ci->session->set_flashdata('warning',$error_message);
							return FALSE;
						}else{
							if($response_description || $error_message){
								$update = array(
									'response_code' => $response_code,
									'response_description' => $response_description,
									'checkout_request_id' => $checkout_request_id,
									'merchant_request_id' => $merchant_request_id,
									'customer_message' => $customer_message,
									'modified_on' => time(),
								);
								if($this->ci->safaricom_m->update_stkpushrequest($id,$update)){
									//return $this->ci->safaricom_m->get_stk_request($id);
									return $response;
								}else{
									$this->ci->session->set_flashdata('warning',"Error occured receiving response. Try again later");
									return FALSE;
								}
							}else{
								$this->ci->session->set_flashdata('warning',"Could not make payment at the moment. Error occured. Try again later.");
								return FALSE;
							}
						}
					}else{
						$this->ci->session->set_flashdata('warning',"invalid response received. Try again later");
						return FALSE;
					}
				}else{
					$this->ci->session->flashdata('warning');
					return FALSE;
				}
	        }else{
	        	$this->ci->session->set_flashdata('warning','Transaction request failed. Try again');
				return FALSE;
	        }
  		}else{
  			$this->ci->session->set_flashdata('warning','Could not proceed with the payment request payment particulars empty(var). Try again later');
            return FALSE;
  		}
  	}

  	public function mpesa($auth=array()){
		if($auth){
			$sms_success_entries = TRUE;
			$email_success_entries = TRUE;
			$auth = (object)$auth;
			if($auth->request_id == 2){
				if(valid_phone($auth->phone)){
					$message = $this->ci->sms_m->build_sms_message('user-activation-code',array(
						'FIRST_NAME'=>$auth->first_name,
						'PIN'=>$auth->pin,
						'APPLICATION_NAME'	=>$this->settings->application_name,
					));
					$sms_particulars =  array(
						'to'=>$auth->phone,
						'message'=>$message,
					);
					if($this->send_sms_via_africastalking_api($sms_particulars)){
						return TRUE;
					}else{
						$this->ci->session->set_flashdata('warning','could not send email address');
						//echo $this->ci->email->print_debugger();
						return FALSE;
					}
				}
			}
			if($auth->request_id == 1){
				if(valid_email($auth->email)){
					$subject = $this->settings->application_name.' activation code';
					$message = $this->ci->emails_m->build_email_message('user-activation-code',array(
						'PIN'=>$auth->pin,
						'FIRST_NAME'=>$auth->first_name,
						'APPLICATION_NAME'	=>	$this->settings->application_name,
						'RECIPIENT_EMAIL' => $auth->email,
						'SENDER_EMAIL'=>'info@rota.com',
						'SUBJECT' => $subject,
						'LINK'=>$auth->link,
						'LINK_ALT'=>$auth->link,
						'YEAR' => date('Y'),
					));
					$email_particulars =  array(
						'headers'=>'',
						'subject'=>"Account Verification",
						'email_to'=>$auth->email,
						'message'=>$message,
					);
					if($this->send_email_via_sendgrid_api($email_particulars)){
						return TRUE;
					}else{
						$this->ci->session->set_flashdata('warning','could not send email address');
						return FALSE;
					}
				}else{
					return FALSE;
				}
			}
			/*if($sms_success_entries){
				return TRUE;
			}else{
				return FALSE;
			}*/
		}else{
			return FALSE;
		}
	}

	public function generate_token(){
        $credentials = base64_encode($this->mpesa['consumer_key'] . ':' . $this->mpesa['consumer_secret']);
        $headers = array('Authorization: Basic ' . $credentials);
        $response = $this->ci->curl->mpesa_generate_token($this->mpesa['live_token_url'],$credentials,$headers);
        return $response;
        /*$curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->mpesa['sandbox_token_url']);
        $credentials = base64_encode($this->mpesa['consumer_key'] . ':' . $this->mpesa['consumer_secret']);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);

        return json_decode($curl_response)->access_token;*/
    }

    public function generate_checkout_token(){
    	$credentials = base64_encode($this->mpesa['checkout_consumer_key'] . ':' . $this->mpesa['checkout_consumer_secret']);
        $headers = array('Authorization: Basic ' . $credentials);
        $response = $this->ci->curl->mpesa_generate_token($this->mpesa['live_token_url'],$credentials,$headers);
        return $response;
    }

    public function register_url($register_particulars = array()){
    	if($register_particulars){
    		$register_object = (object)$register_particulars;
    		$url = $this->mpesa['sandbox_url'] . 'c2b/v1/registerurl';
	        $data = array(
	            'ShortCode' => $register_object->short_code,
	            'ResponseType' => $register_object->response_type, //Completed or Cancelled
	            'ConfirmationURL' => $register_object->confirm_url,
	            'ValidationURL' => $register_object->validate_url,
	        );
	        $response = $this->ci->curl->post_json_payment($data,$url);
        	return json_decode($response);
    	}else{
    		$this->ci->session->set_flashdata('warning','Register url particulars missing');
    		return FALSE;
    	}
    }

    public function c2b($c2b_particulars = array()){
    	if($c2b_particulars){
    		$c2b_object = (object)$c2b_particulars;
    		$url = $this->mpesa['sandbox_url'] . 'c2b/v1/simulate';
	        $particulars = array(
                'ShortCode'=>$c2b_object->short_code,
                'Msisdn'=>$c2b_object->Msisdn,
                'CommandID'=>$c2b_object->CommandID,
                'Amount'=>$c2b_object->Amount,
                'BillRefNumber'=>$c2b_object->BillRefNumber,
            );
          //  print_r($particulars);die();
	        $response = $this->ci->curl->post_json_payment($particulars,$url);
        	return json_decode($response);
    	}else{
    		$this->ci->session->set_flashdata('warning','Register url particulars missing');
    		return FALSE;
    	}
    }

    public function stk_push_simulation($simulation_particulars = array()){
    	if($simulation_particulars){
    		$simulation_object = (object)$simulation_particulars;
    		$url = $this->mpesa['sandbox_url'] . 'stkpush/v1/processrequest';
        	$timestamp = date("Ymdhis");
        	$password = base64_encode($simulation_object->BusinessShortCode . $this->mpesa['lipa_na_mpesa_passkey'] . $timestamp);
	        $particulars = array(
                'BusinessShortCode' => $simulation_object->BusinessShortCode,
            	'Password' => $password,
            	'Timestamp' => $timestamp,
            	'TransactionType' => $simulation_object->TransactionType,
            	'Amount' => $simulation_object->Amount,
            	'PartyA' => $simulation_object->PartyA,
            	'PartyB' => $simulation_object->PartyB,
            	'PhoneNumber' => $simulation_object->PhoneNumber,
            	'CallBackURL' => $simulation_object->CallBackURL,
            	'AccountReference' => $simulation_object->AccountReference,
            	'TransactionDesc' => $simulation_object->TransactionType,
            	'Remark' => $simulation_object->TransactionDesc,
            );
            //print_r($particulars); die();
	        $response = $this->ci->curl->post_json_checkout_payment($particulars,$url);
        	return json_decode($response);
    	}else{
    		$this->ci->session->set_flashdata('warning','Register url particulars missing');
    		return FALSE;
    	}
    }

}
