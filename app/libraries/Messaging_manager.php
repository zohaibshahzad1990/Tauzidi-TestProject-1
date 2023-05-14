<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once APPPATH . 'third_party/vendor/autoload.php';
use AfricasTalking\SDK\AfricasTalking;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Notification;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;


class Messaging_manager{

	protected $ci;
	public $settings;

  	public function __construct(){
		$this->ci= & get_instance();		
    	$this->ci->load->model('sms/sms_m');
    	$this->ci->load->model('emails/emails_m');
    	$this->ci->load->config('email', TRUE);
    	$this->ci->load->config('fcm', TRUE);
    	$this->ci->load->config('africastalking', TRUE);
    	$this->africastalking = $this->ci->config->item('africastalking');
        $this->config = $this->ci->config->item('email');
        $this->fcm = $this->ci->config->item('fcm');
		$this->settings = $this->ci->settings_m->get_settings(1)?:'';
  	}

  	public function send_user_otp($auth=array()){
		if($auth){
			$sms_success_entries = TRUE;
			$email_success_entries = TRUE;
			$auth = (object)$auth;
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
				//print_r($sms_particulars); die();
				if($this->send_sms_via_africastalking_api($sms_particulars)){
				//if($this->send_sms_via_tumaweb($sms_particulars)){
					return TRUE;
				}else{
					$this->ci->session->set_flashdata('warning','could not send email address');
					//echo $this->ci->email->print_debugger();
					return FALSE;
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
		}else{
			return FALSE;
		}
	}

	function send_password_recovery_email($identity='',$subject ='',$message = ''){
		if($identity
			&&$message){
			$email_particulars =  array(
				'headers'=>'',
				'subject'=>"Password Reset",
				'email_to'=>$identity,
				'message'=>$message,
			);
			if($this->send_email_via_sendgrid_api($email_particulars)){
				return TRUE;
			}else{
				$this->ci->session->set_flashdata('warning','could not send email address');
				echo $this->ci->email->print_debugger();
				return FALSE;
			}
		}else{
			$this->ci->session->set_flashdata('warning','could not send email address');
			//echo $this->ci->email->print_debugger();
			return FALSE;
		}

	}

	function send_system_sms($identity='',$message = ''){
		if($identity
			&&$message){
			$sms_particulars =  array(
				'to'=>$identity,
				'message'=>$message,
			);
			if($this->send_sms_via_tumaweb($sms_particulars)){
				return TRUE;
			}else{
				$this->ci->session->set_flashdata('warning','could not send email message');
				//echo $this->ci->email->print_debugger();
				return FALSE;
			}
		}else{
			$this->ci->session->set_flashdata('warning','could not send sms message');
			//echo $this->ci->email->print_debugger();
			return FALSE;
		}
	}

	function notify_user_password_change($user=array()){
		if($user){
			$user = (object)$user;
			$message = $this->ci->emails_m->build_email_message('password-changed-successfully',array(
				'FIRST_NAME' => $user->first_name
			));
			$email_particulars =  array(
				'headers'=>'',
				'subject'=>"Password Successfully Changed",
				'email_to'=>$user->email,
				'message'=>$message,
			);
			if($this->send_email_via_sendgrid_api($email_particulars)){
				return TRUE;
			}else{
				$this->ci->session->set_flashdata('warning','Could not send email address');
			}
		}else{
			return FALSE;
		}
	}

	function send_email_via_sendgrid_api($email_array= array()){
        if(empty($email_array)){
            $this->ci->session->set_flashdata('warning','Email particulars array is empty');
            return FALSE;
        }else{
            $email_particulars = (object)$email_array;
            if($email_particulars){
            	$email_address = 'info@educationKe.com';
                $email = new \SendGrid\Mail\Mail(); 
                $email->setFrom($email_address);
                $email->setSubject($email_particulars->subject);
                $email->addTo($email_particulars->email_to);
                $email->addContent(
                    "text/html", $email_particulars->message
                );             
                $sendgrid = new \SendGrid($this->config['apiKey']);               
                try {
                    $response = $sendgrid->send($email);
                    $status_code = $response->statusCode();
                    if($status_code == '202' || $status_code == '200'){
                        return TRUE;
                    }else{
                        return FALSE;
                    }  
                } catch (Exception $e) {
                	print_r($this->ci->session->set_flashdata('warning',$e->getMessage())); die();
                	$this->ci->session->set_flashdata('warning',$e->getMessage());
                    echo 'Caught exception: '. $e->getMessage() ."\n";
                }
            }else{
               return FALSE;  
            }
        }        

    }

    function send_sms_via_africastalking_api($sms_array= array()){
        if(empty($sms_array)){
            $this->ci->session->set_flashdata('warning','Sms particulars array is empty');
            return FALSE;
        }else{
            $sms_particulars = (object)$sms_array;
            if($sms_particulars){
            	$to = $sms_particulars->to;
            	$message = $sms_particulars->message;
                $username = 'sasatips'; // use 'sandbox' for development in the test environment
				$apiKey = $this->africastalking['apiKey']; // use your sandbox app API key for development in the test environment
				$AT= new AfricasTalking($username, $apiKey);
				//Get one of the services
				//print_r($message); die;
				$sms = $AT->sms();
				// Use the service
				$result = $sms->send([
				    'to'      => $to,
				    'message' => $message 
				]);
				//print_r($message); die;
				if($result){
                    if($result['status'] == 'success'){
                        if(empty($result['data']->SMSMessageData->Recipients)){
                            $this->ci->session->set_flashdata('warning',$result['data']->SMSMessageData->Message);
                            return FALSE;
                        }else{
                            $this->ci->session->set_flashdata('warning',$result['data']->SMSMessageData->Message);
                            return $result['data']->SMSMessageData->Message;
                        }
                    }
                }else{
                    return FALSE;
                } 
            }else{
               return FALSE;  
            }
        } 
    }

    function send_push_notification_via_fcm($sms_array= array()){
        if(empty($sms_array)){
            $this->ci->session->set_flashdata('warning','Sms particulars array is empty');
            return FALSE;
        }else{
            $sms_particulars = (object)$sms_array;
            if($sms_particulars){
            	$token = $sms_particulars->token;
            	$new_message = $sms_particulars->message;
            	$title = $sms_particulars->title;

            	$tokens = array($token);
				$message_complete = array("body" => $new_message, "title" => $title);
				$message_status = $this->send_notification($tokens, $message_complete);
				//echo $message_status->; die();
				//echo $message_status->; die();
				return  $message_status;
            	/*$server_key = $this->fcm['apiKey'];
				$client = new Client();
				$client->setApiKey($server_key);
				$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

				$message = new Message();
				$message->setPriority('normal');
				$message->addRecipient(new Device($sms_particulars->token));
				$message
				    ->setNotification(new Notification($title, $new_message));
				$result = $client->send($message);

				if($result){
                    if($result->getStatusCode() == 200){
                        return $result->getBody()->getContents();
                    }
                }else{
                    return FALSE;
                } */



            }else{
               return FALSE;  
            }
        } 
    }

    function send_notification ($tokens, $message_complete){
	    $url = 'https://fcm.googleapis.com/fcm/send';
	    $fields = array(
	        'registration_ids' => $tokens,
	        'notification' => $message_complete
	    );
	    $server_key = $this->fcm['apiKey'];
	    $headers = array(
	        'Authorization:key ='.$server_key, //Change API KEY HERE
	        'Content-Type: application/json'
	    );

	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
	    
	    $result = curl_exec($ch);           

	    if ($result === FALSE) {
	        die('Curl failed: ' . curl_error($ch));
	    }
	    curl_close($ch);
	    return $result;
	}

	function send_sms_via_tumaweb($sms_array= array()){
        if(empty($sms_array)){
            $this->ci->session->set_flashdata('warning','Sms particulars array is empty');
            return FALSE;
        }else{
            $sms_particulars = (object)$sms_array;

            if($sms_particulars){

            	$sms_object = (object)[
            		"recipient" => $sms_particulars->to,
					"message"=> $sms_particulars->message,
					"unique_ref" => generate_random_string(10),
					"dlr_url" => $this->africastalking['callBackUrl'],
					"message_type"=> "T",
					"product_id" => $this->africastalking['profileID']
            	];

            	$url = $this->africastalking['tumaURLOne'];
			    $headers = array(
			        'Api-Key:'.$this->africastalking['tuzidiKey'], //Change API KEY HERE
			        'Content-Type: application/x-www-form-urlencoded'
			    );
			    //print_r(json_encode($sms_object));
			    //print_r($headers); die();
			    $ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $url);
			    curl_setopt($ch, CURLOPT_POST, true);
			    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			    curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);  
			    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($sms_object));
			    
			    $result = curl_exec($ch);           

			    if ($result === FALSE) {
			        //die('Curl failed: ' . curl_error($ch));
			        return FALSE;
			    }
			    curl_close($ch);
			    //print_r(json_decode($result)); die();
			    $res = json_decode($result);
			    if($res && $res->sms_count == 1){
			    	return $res;
			    }else{
			    	return FALSE;
			    }
            	
            }else{
               return FALSE;  
            }
        } 
	}

    function create_and_queue_email($send_user=array(),$message='',$sending_user = array(),$subject='',$attachments=array(),$ccs=array(),$bccs=array(),$embeded_attachments=array(),$send=1){
		if(is_array($send_user)&&$message&&is_object($sending_user)&&$subject){
			if(!empty($send_user)){
				$success = 0;
				$fails = 0;
				$educationKe_team = 'aggreykoros04@gmail.com';
			    foreach ($send_user as $member) {
	                if($member=='educationKe-team'){
	                    $email = $educationKe_team;
	                    $cc = '';
	                    $bcc = '';
	                    foreach ($ccs as $value) {
	                        if($cc){
	                            if($value=='educationKe-team')
	                            {
	                                $cc = $cc.','.$educationKe_team;
	                            }else{
	                                $cc = $cc.','.$value->email;
	                            }
	                        }else{
	                            if($value=='educationKe-team')
	                            {
	                                $cc = $educationKe_team;
	                            }else{
	                                $cc = $value->email;
	                            }
	                        }
	                    }
	                    foreach ($bccs as $value) {
	                        if($bcc){
	                            if($value=='educationKe-team')
	                            {
	                                $bcc = $bcc.','.$educationKe_team;
	                            }elseif($value=='group-email'){
	                                $bcc = $bcc.','.$group->email;
	                            }else{
	                                $bcc = $bcc.','.$value->email;
	                            }
	                        }else{
	                            if($value=='educationKe-team')
	                            {
	                                $bcc = $educationKe_team;
	                            }elseif($value=='group-email'){
	                                $bcc = $bcc.','.$group->email;
	                            }else{
	                                $bcc = $value->email;
	                            }
	                        }
	                    }

	                    if($send){
	                        $is_draft = 0;
	                    }else{
	                        $is_draft = 1;
	                    }

	                    $email_data = array(
	                        'MAIL_TO' => $this->settings->application_name.' Team',
	                        'MAIL_FROM' => $sending_user->first_name,
	                        'SUBJECT' => $subject,
	                        'EMAIL_BODY' => $message,
	                        'NAME' => 'System Email',
	                        'GROUP_NAME' => $group->name,
	                        'TIME' => timestamp_to_receipt(time()),
	                        'YEAR' => date('Y',time()),
	                        'PRIMARY_THEME_COLOR'=>$this->settings->primary_color,
	                        'TERTIARY_THEME_COLOR'=>$this->settings->tertiary_color,
	                        'APPLICATION_LOGO'=>$this->settings?site_url('/uploads/logos/'.$this->application_settings->logo):'', 

	                    );
	                    print_r($email_data); die();
	                    $email_body = $this->ci->emails_m->build_email_message('general-mailing-template',$email_data);
	                    
	                    $id = $this->ci->emails_m->insert_email_queue(array(
	                            'email_to'  =>  $email,
	                            'subject'   =>  $subject,
	                            'message'   =>  $email_body,
	                            'email_from'=>  $sending_user->email,
	                            'sending_email' => '',
	                            'group_id'  =>  $group_id,
	                            'member_id' =>  1,
	                            'user_id'   =>  1,
	                            'attachments'=> serialize($attachments),
	                            'cc'        =>  $cc,
	                            'bcc'       =>  $bcc,
	                            'embeded_attachments' => serialize($embeded_attachments),
	                            'is_draft'  =>  $is_draft?:0,
	                            'created_on'    =>  time(),
	                            'created_by'    =>  $sending_user->id
	                        ));
	                    if($id){
	                        ++$success;
	                    }
	                    else{
	                        ++$fails;
	                    }
	                }elseif($member=='group-email'){
	                    $cc = '';
	                    $bcc = '';
	                    foreach ($ccs as $value) {
	                        if($cc){
	                            if($value=='educationKe-team')
	                            {
	                                $cc = $cc.','.$educationKe_team;
	                            }elseif($value=='group-email'){
	                                $cc = $cc.','.$group->email;
	                            }else{
	                                $cc = $cc.','.$value->email;
	                            }
	                        }else{
	                            if($value=='educationKe-team')
	                            {
	                                $cc = $educationKe_team;
	                            }elseif($value=='group-email'){
	                                $cc = $cc.','.$group->email;
	                            }else{
	                                $cc = $value->email;
	                            }
	                        }
	                    }
	                    foreach ($bccs as $value) {
	                        if($bcc){
	                            if($value=='educationKe-team')
	                            {
	                                $bcc = $bcc.','.$educationKe_team;
	                            }elseif($value=='group-email'){
	                                $bcc = $bcc.','.$group->email;
	                            }else{
	                                $bcc = $bcc.','.$value->email;
	                            }
	                        }else{
	                            if($value=='educationKe-team')
	                            {
	                                $bcc = $educationKe_team;
	                            }elseif($value=='group-email'){
	                                $bcc = $bcc.','.$group->email;
	                            }else{
	                                $bcc = $value->email;
	                            }
	                        }
	                    }

	                    if($send){
	                        $is_draft = 0;
	                    }else{
	                        $is_draft = 1;
	                    }

	                    $email_data = array(
	                        'MAIL_TO' => 'Group email',
	                        'MAIL_FROM' => $sending_user->first_name,
	                        'SUBJECT' => $subject,
	                        'EMAIL_BODY' => $message,
	                        'GROUP_NAME' => $group->name,
	                        'NAME' => $group->name,
	                        'TIME' => timestamp_to_receipt(time()),
	                        'YEAR' => date('Y',time()),
	                        'PRIMARY_THEME_COLOR'=>$this->application_settings->primary_color,
	                        'TERTIARY_THEME_COLOR'=>$this->application_settings->tertiary_color,
	                        'APPLICATION_LOGO'=>$this->application_settings?site_url('/uploads/logos/'.$this->application_settings->logo):'', 

	                    );

	                    $email_body = $this->ci->emails_m->build_email_message('general-mailing-template',$email_data);
	                    $id = $this->ci->emails_m->insert_email_queue(array(
	                        'email_to'  =>  $group->group_email,
	                        'subject'   =>  $subject,
	                        'message'   =>  $email_body,
	                        'email_from'=>  $sending_user->email,
	                        'sending_email' => '',
	                        'group_id'  =>  $group_id,
	                        'member_id' =>  1,
	                        'user_id'   =>  1,
	                        'attachments'=> serialize($attachments),
	                        'cc'        =>  $cc,
	                        'bcc'       =>  $bcc,
	                        'embeded_attachments' => serialize($embeded_attachments),
	                        'is_draft'  =>  $is_draft?:0,
	                        'created_on'    =>  time(),
	                        'created_by'    =>  $sending_user->id
	                    ));
	                    if($id){
	                        ++$success;
	                    }
	                    else{
	                        ++$fails;
	                    }
	                }else if(valid_email($member->email)){
	                    $cc = '';
	                    $bcc = '';
	                    foreach ($ccs as $value) {	                    	
	                        if($cc){
	                        	$email = isset($value->email)?$value->email:'';
	                        	if($email){                         
	                            	$cc = $cc.','.$value->email;
	                            }
	                        }else{	                            
	                            $email = isset($value->email)?$value->email:'';
	                        	if($email){                         
	                            	$cc = $cc.','.$value->email;
	                            }
	                        }
	                    }
	                    foreach ($bccs as $value) {
	                        if($bcc){
	                            if($value=='educationKe-team')
	                            {
	                                $bcc = $bcc.','.$educationKe_team;
	                            }else{
	                            	$email = isset($value->email)?$value->email:'';
		                        	if($email){                         
		                            	$bcc = $bcc.','.$value->email;
		                            }	                                
	                            }
	                        }else{
	                            if($value=='educationKe-team')
	                            {
	                                $bcc = $educationKe_team;
	                            }else{
	                                $email = isset($value->email)?$value->email:'';
		                        	if($email){                         
		                            	$bcc = $bcc.','.$value->email;
		                            }
	                            }
	                        }
	                    }

	                    if($send){
	                        $is_draft = 0;
	                    }else{
	                        $is_draft = 1;
	                    }

	                    $email_data = array(
	                        'MAIL_TO' => $member->first_name,
	                        'MAIL_FROM' => $sending_user->first_name,
	                        'SUBJECT' => $subject,
	                        'EMAIL_BODY' => $message,
	                        'NAME' => $member->first_name.' '.$member->last_name,
	                        'TIME' => timestamp_to_receipt(time()),
	                        'YEAR' => date('Y',time()),
	                        //'PRIMARY_THEME_COLOR'=>$this->settings->primary_color,
	                        //'TERTIARY_THEME_COLOR'=>$this->settings->tertiary_color,
	                        //'APPLICATION_LOGO'=>$this->settings?site_url('/uploads/logos/'.$this->settings->logo):'', 

	                    );	                    
	                    $email_body = $this->ci->emails_m->build_email_message('general-mailing-template',$email_data);
	                    //print_r($email_body); die();
	                    $id = $this->ci->emails_m->insert_email_queue(array(
	                            'email_to'  =>  $member->email,
	                            'subject'   =>  $subject,
	                            'message'   =>  $email_body,
	                            'email_from'=>  $sending_user->email,
	                            'sending_email' => '',
	                            //'group_id'  =>  $group_id,
	                            //'member_id' =>  $member->id,
	                            'user_id'   =>  $member->id,
	                            'attachments'=> serialize($attachments),
	                            'cc'        =>  $cc,
	                            'bcc'       =>  $bcc,
	                            'embeded_attachments' => serialize($embeded_attachments),
	                            'is_draft'  =>  $is_draft?:0,
	                            'created_on'    =>  time(),
	                            'created_by'    =>  $sending_user->id
	                        ));
	                    if($id){
	                        ++$success;
	                    }
	                    else{
	                        ++$fails;
	                    }

	                }else{
	                    ++$fails;
	                }
	                if($success){
	                    if($is_draft){
	                        $this->ci->session->set_flashdata('success',$success.' Email(s) Successfully saved to draft.');
	                    }else{
	                        $this->ci->session->set_flashdata('success',$success.' Email(s) Successfully Queued. Will be sent shortly.');
	                    }
	                }if($fails){
	                    $this->ci->session->set_flashdata('error','unable to queue '.$fails.' Email(s) and thus will not be sent');
	                }
	            }
			}else{
				$this->ci->session->set_flashdata('error','There are no members with valid email addresses');
				return FALSE;
			}
		}else{
			$this->ci->session->set_flashdata('error','Some essential parameters are not available');
			return FALSE;
		}
	}

	function queue_invite_email($invite_array = array()){
		if($invite_array){
			$count = 0;
			$fails = 0;
			foreach ($invite_array as $key => $invite) {
				$invite = (object)$invite;
				if(valid_email($invite->email)){
					$message = $this->ci->emails_m->build_email_message('invite-student',array(
						'FIRST_NAME' => $invite->first_name,
						'CLASS_NAME' => $invite->class_name,
						'LINK' =>'https://education-ke.web.app/auth/join/'.$invite->invite_code,
						'TEACHER_NAME' => $invite->teacher_name, 
					));					
					$input = array(
						'email_to'=>$invite->email,
						'subject'=>$invite->teacher_name.' has invited you to join '.$invite->class_name,
						'email_from'=>'',
						'user_id'=>$invite->user_id,
						'message'=>$message,
						'created_on'=>time(),
						'created_by'=>$invite->user_id
					);
					
					$result = $this->ci->emails_m->insert_email_queue($input);
					if($result){
						$count++;
					}else{
						$fails++;
					}
				}
			}

		}else{
			$this->ci->session->set_flashdata('warning','invite array is empty');
            return FALSE;
		}
	}

	function queue_invite_sms($invite_object = ""){
		if($invite_object){
			$count = 0;
			$fails = 0;
			$invite = (object)$invite_object;
			if(valid_phone($invite->sms_to)){
				$message = $this->ci->sms_m->build_sms_message('driver-invite',array(
					'FIRST_NAME' => $invite->first_name,
					'APPLICATION_NAME' => $this->settings->application_name,
					'PIN' => $invite->confirmation_code
				));					
				$input = array(
					'sms_to'=>$invite->sms_to,
					'user_id'=>$invite->user_id,
					'message'=>$message,
					'created_on'=>time(),
					'created_by'=>$invite->created_by
				);
				
				$result = $this->ci->sms_m->insert_sms_queue($input);
				if($result){
					$count++;
				}else{
					$fails++;
				}
			}
		}else{
			$this->ci->session->set_flashdata('warning','invite object is empty');
            return FALSE;
		}
	}

}
