<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Transactions{
	  protected $ci;
    	public $paybills = array(
        
    );

	protected $payment_types = array(
		1 => 'M-PESA STK PUsh Payment',
      	2 => 'M-PESA Direct payment',
      	3 => 'B2C Transfer'
	);

	public $payment_channels = array(
      	1 => 'M-PESA',
      	2 => 'Equity Bank',
      	3 => 'MTN Mobile',
    );


    public $payment_status = array(
      	1 => 'In progress',
      	2 => 'Response error',
      	3 => 'Result error',
      	4 => 'Successful',
    );

    public $statement_transaction_names = array(
        1 => 'Subscription invoice',
        6=>"Subscription Paymen"
    );

  	public $payable_transaction_types_array = array(1); //reserve 1-5
	public $paid_transaction_types_array = array(6); // from 6->->

	public function __construct(){
		$this->ci= & get_instance();
	    set_time_limit(0);
	    ini_set('memory_limit','2048M');
	    ini_set('max_execution_time', 1200);
	    //$this->ci->load->model('safaricom/safaricom_m');
	    $this->ci->load->model('transactions/transactions_m');
	    $this->ci->load->model('deposits/deposits_m');
	    $this->ci->load->model('users/users_m');
	    $this->ci->load->model('billing/billing_m');
		$this->ci->load->model('notifications/notifications_m');
		$this->ci->load->model('statements/statements_m');
		$this->ci->load->library('Curl');
		$this->ci->load->library('billing_manager');
		$this->ci->load->library('notification_manager');
		
	}

	public function subscribe($package = array()){
     	if($package){
     		$package = (object)$package;
     		if($package->billing_package_id){
                $billing = $this->ci->billing_m->get($package->billing_package_id);
     		}else{
     			$billing = $this->ci->billing_m->check_if_default();
     		}
     		$earliest_invoice_date = time();
     		if($billing){
     			if($billing->billing_type_frequency == 1){
     				$end_date = strtotime("+ 7 days");
     			}else if($billing->billing_type_frequency == 2){
     				$end_date = strtotime("+ 30 days");
     			}else if($billing->billing_type_frequency == 3){
     				$end_date = strtotime("+ 12 months");
     			}else{
     				$end_date = strtotime("+ 7 days");
     			}
     			$input = array(
	                'type'=>1,
	                'invoice_number'=>'INV-'.$this->ci->invoices_m->calculate_billing_invoice_number($package->user->id),
	                'user_id'=>$package->user->id,
	                'invoice_date'=>time(),
	                'due_date'=>'',
	                'billing_package_id'=>$billing->id,
	                'subcription_end_date'=>$end_date,
	                'amount_payable'=>currency($billing->amount),
	                'amount_paid'=>0,
	                'description'=>"Subscription Invoice",
	                'active'=>1,
	                'created_on'=>time(),
	            );
	            if($invoice_id = $this->ci->invoices_m->insert($input)){
	                $input = array(
	                    'transaction_type'=>1,
	                    'transaction_date'=>time(),
	                    'user_id'=>$package->user->id,
	                    'invoice_id'=>$invoice_id,
	                    'amount'=>currency($billing->amount),
	                    'description'=>"Subscription Invoice",
	                    'active'=>1,
	                    'created_on'=>time(),
	                );
	                if($statement_entry_id = $this->ci->statements_m->insert($input)){
	                	if($this->update_user_statement_balances($package->user->id,$earliest_invoice_date)){
	                		return TRUE;
	                	}else{
	                		$this->ci->session->set_flashdata('warning','Could not update user statements');
      						return FALSE;
	                	}	                    
	                }else{
	                    //could not insert statement entry
	                    $this->ci->session->set_flashdata('warning','could not insert statement entry');
	                    return FALSE;
	                }
	            }else{
	                //could not insert invoice entry
	                $this->ci->session->set_flashdata('warning','could not insert invoice entry');
	                return FALSE;
	            }
     		}else{
     			$this->ci->session->set_flashdata('warning','Billing package is missing');
      			return FALSE;	
     		}
      	}else{
      		$this->ci->session->set_flashdata('warning','Subscription details is empty(var)');
      		return FALSE;
     	}
    }

 	function update_user_statement_balances($user_id = 0,$date = 0){
	    if($user_id){
			//$latest_statement_entries = $this->ci->statements_m->get_user_latest_statement_entries($user_id,$date);
			$statement_entries = $this->ci->statements_m->get_user_subscription_statements($user_id,$date);
	        $user_subcription_balances_array = array();
	        $user_subcription_paid_array = array();
	        $user_cumulative_balances_array = array();
	        $user_cumulative_paid_array = array();
	        if($statement_entries){
	            $statement_entries_array = array();
	            $invoice_ids = array();
	            $statement_ids = array();
	            foreach($statement_entries as $statement_entry):
	            	$user_subcription_paid_array[$statement_entry->user_id] = 0;

	            	$user_cumulative_paid_array[$statement_entry->user_id]= 0;

	            	$user_subcription_balances_array[$statement_entry->user_id] = 0;

	                $user_cumulative_balances_array[$statement_entry->user_id] = 0;	

	                $statement_ids[] = $statement_entry->id;

	            endforeach;

	            foreach($statement_entries as $statement_entry):                
	                $cumulative_balance = 0;
	                $subscription_balance = 0;
	                $cumulative_paid = 0;
	                $subscription_paid = 0;

	                /*payables */
	                if(in_array($statement_entry->transaction_type,$this->payable_transaction_types_array)){
                      	if(valid_currency($statement_entry->amount)){

                          	$user_subcription_balances_array[$statement_entry->user_id] += currency($statement_entry->amount);

                          	$user_cumulative_balances_array[$statement_entry->user_id] += currency($statement_entry->amount);
                      
                          	$subscription_balance = $user_subcription_balances_array[$statement_entry->user_id];

                          	$cumulative_balance = $user_cumulative_balances_array[$statement_entry->user_id]; 

	                          /*if(in_array($statement_entry->transaction_type,$this->paid_deductable_transaction_types_array)){

	                              $user_subcription_paid_array[$statement_entry->user_id] -= currency($statement_entry->amount);
	                              $user_cumulative_paid_array[$statement_entry->user_id] -= currency($statement_entry->amount);
	                          }*/

                          	$subscription_paid = $user_subcription_paid_array[$statement_entry->user_id];
                          	$cumulative_paid = $user_cumulative_paid_array[$statement_entry->user_id];

                      	}
	                }

                  	/*paids*/
                  	if(in_array($statement_entry->transaction_type,$this->paid_transaction_types_array)){
                        if(valid_currency($statement_entry->amount)){

                           	$user_subcription_balances_array[$statement_entry->user_id] -= currency($statement_entry->amount);
                          	$user_cumulative_balances_array[$statement_entry->user_id] -= currency($statement_entry->amount);

                          	$user_subcription_paid_array[$statement_entry->user_id] += currency($statement_entry->amount);

                          	$user_cumulative_paid_array[$statement_entry->user_id] += currency($statement_entry->amount);

                          	$subscription_balance = $user_subcription_balances_array[$statement_entry->user_id];
                          	$cumulative_balance = $user_cumulative_paid_array[$statement_entry->user_id]; 



                          	$subscription_paid = $user_subcription_paid_array[$statement_entry->user_id];
                          	$cumulative_paid = $user_cumulative_paid_array[$statement_entry->user_id]; 
                          	//echo $cumulative_paid."<br/>";
                        }
                  	}

	                $statement_entries_array[] = array(
	                    'statement_type' => 1,
	                    'transaction_type' => $statement_entry->transaction_type,
	                    'transaction_date' => $statement_entry->transaction_date,	                    
	                    'user_id' => $statement_entry->user_id,	                    
	                    'invoice_id' => $statement_entry->invoice_id,
	                    'amount' => $statement_entry->amount,
	                    'subscription_balance' => $subscription_balance,
	                    'balance' => $cumulative_balance,
	                    'subscription_paid' => $subscription_paid,
	                    'cumulative_paid' => $cumulative_paid,
	                    'bill_payment_id'=>$statement_entry->bill_payment_id,
	                    'active' => $statement_entry->active,
	                    'created_by' => $statement_entry->created_by,
	                    'created_on' => $statement_entry->created_on,
	                    'modified_on' => $statement_entry->modified_on,
	                    'modified_by' => $statement_entry->modified_by,
	                    'account_id' => isset($statement_entry->account_id)?$statement_entry->account_id:"",
	                    'description' => isset($statement_entry->description)?$statement_entry->description:'',
	                    'deposit_id' => isset($statement_entry->deposit_id)?$statement_entry->deposit_id:'',
	                    'old_statement_id'=> $statement_entry->id,
	                );

	            endforeach;
	            if(empty($statement_entries_array)){//this is where the error is ..Kindly review
	                return FALSE;
	            }else{
	                if($statement_insert_result = $this->ci->statements_m->insert_statements_batch($statement_entries_array)){
	                    if($statement_ids){
	                        if($this->ci->statements_m->void_user_statements_by_ids_array($statement_ids)){
	                            return TRUE;
	                        }else{
	                            return FALSE;
	                        }
	                    }else{
	                        return TRUE;
	                    }                        
	                    //return TRUE;
	                }else{
	                    return FALSE;
	                }
	            }

	        }else{
	        	return TRUE;
	        }
	  	}else{
	  		$this->ci->session->set_flashdata('warning','User id varible is required');
	    	return FALSE;
	  	}
  	}

    function make_online_payment($user=array(),$transaction=array(),$initiate_request =1,$phone=0,$currency = "KES"){
        if($user && $transaction){
          	$phone = valid_phone($phone)?:$user->phone;
	        $input = array(
              	'invoice_id' => $transaction->invoice_id,
              	'description' => $transaction->description,
              	'amount' => $transaction->amount,
              	'reference_number' => '',
              	"user_id" => $user->id,
              	"amount" => $transaction->amount,
              	"phone" => $transaction->phone,
              	"status" => 1,
              	"active" => 1,
              	"created_on" => time(),
              	"created_by" => $user->id,
	        );
          	if($id = $this->ci->deposits_m->insert_online_payment_request($input)){
              	$reference_number = time()+$id;
              	$payment = array(
                	'callback_url' =>'https://api.educationke.com/api/v1/transactions/online_payment',
                	'amount' => $transaction->amount,
                	'invoice_id' => $transaction->invoice_id,
                	'reference_number'=> $reference_number,
                	'user'=>$user,
                	'phone' => $transaction->phone,
              	);
              	if($response = $this->ci->mpesa->initiate_online_checkout($payment)){
                	return $response;
              	}else{
                	$this->ci->session->set_flashdata('warning','Service currently experiencing technical hitches. Kindly try again later');
               		return FALSE;
              	}
          	}else{
            	$this->ci->session->set_flashdata('error','Error occured. Could not proceed with the payment request. Try again later');
            	return FALSE;
          	}
      	}else{
        	$this->ci->session->set_flashdata('error','Essential parameters are missing');
        	return FALSE;
      	}
  	}

  	public function record_transaction($payment_transaction = array()){
  		if($payment_transaction){  			
        	$amount = currency($payment_transaction->amount);
       		if($amount){
          		$transaction_id = $payment_transaction->transaction_id;
          		//if(TRUE){
          		if($this->ci->deposits_m->is_unique_deposit($transaction_id)){          			
          			$user = $this->ci->users_m->get_user_by_phone_number($payment_transaction->phone); 
          			if($user){
			            $deposit_data = array(
			             	'deposit_date' => $payment_transaction->transaction_date,
			              	'deposit_method' => 1,
			              	'description' => $payment_transaction->phone,
			              	'transaction_id' => $payment_transaction->transaction_id,
			              	'amount' => $amount,
			              	'invoice_id' => $payment_transaction->invoice_id,
			              	'user_id'=>$user->id,
			              	'phone'=>$payment_transaction->phone,
			              	'active' => 1,
			              	'stk_payment_id' => $payment_transaction->id,
			              	'created_on' => time(),
			            );
			            //if(TRUE){
			            if($deposit_id = $this->ci->deposits_m->insert($deposit_data)){
			            	$new_arrears = $user->arrears - $amount;
			            	$input  = array(
								'arrears'=>$new_arrears,
							);							
							if($this->ci->users_m->update($user->id,$input)){
								if($payment_transaction->invoice_id){

				            		$invoice = $this->ci->invoices_m->get($payment_transaction->invoice_id);

				            		$billing = $this->ci->billing_m->get($invoice->billing_package_id);

									if($billing->billing_type_frequency == 1){
					     				$end_date = strtotime("+ 7 days");
					     			}else if($billing->billing_type_frequency == 2){
					     				$end_date = strtotime("+ 30 days");
					     			}else if($billing->billing_type_frequency == 3){
					     				$end_date = strtotime("+ 12 months");
					     			}else{
					     				$end_date = strtotime("+ 7 days");
					     			}			     			

					     			$amount_payable = $invoice->amount_payable;
					     			if($amount >= $amount_payable){
					     				$amount_to_pay = $amount_payable; 
					     			}else{
					     				$amount_to_pay = $amount;
					     			}
					            	$bill_payment_id = $this->ci->billing_m->insert_billing_payments(array(
						                'user_id' => $user->id,
						                'receipt_date' => $payment_transaction->transaction_date,
						                'billing_receipt_number' => 'RCPT-'.$this->ci->billing_m->calculate_billing_receipt_number($user->id),
						                'amount' => $amount_to_pay,
						                'tax' => $invoice->tax,
						                'payment_method'=> 1,
						                'active'=>1,
						                'billing_package_id'=>$billing->id,
						                'description' =>"Subscription Payment",
						                'transaction_code' => $payment_transaction->transaction_id,
						                'created_on' => time(),
						                'billing_invoice_id'=> $payment_transaction->invoice_id,
						            ));
					            	if($bill_payment_id){
				        				if($amount >= $amount_to_pay){
				        					$is_paid = 1;
				        				}else{
				        					$is_paid = 0; 
				        				}
				        				$update = array(
			    							'amount_paid'=>$amount_to_pay,
			    							'is_auto_pay'=>1,
			    							'is_paid'=>$is_paid,
			    							'modified_on'=>time(),
			    							'modified_by'=>1
			    						);
			    						$this->ci->invoices_m->update($payment_transaction->invoice_id,$update);
						            	$input = array(
						                    'transaction_type'=>6, //subscription payment
						                    'user_id'=>$invoice->user_id,
						                    'transaction_date'=>time(),
						                    'invoice_id'=>$invoice->id,
						                    'bill_payment_id'=>$bill_payment_id,
						                    'description'=>"Subscription Payment",
						                    'amount'=>$amount_to_pay,
						                    'active'=>1,
						                    'created_on'=>time(),
						                );
						                if($this->ci->billing_manager->_reduce_account_balance($user->id,$amount_to_pay,$payment_transaction->invoice_id)){
						                	$update = array(
						                    	'request_reconcilled' => 1,
						                    	'modified_on' => time(),
						                  	);
						                  	$email_data = array(
				                                'DATE' => date('d',time()),
				                                'MONTH' => date('M',time()),
				                                'FIRST_NAME' => $user->first_name,
				                                'LAST_NAME' => $user->last_name,
				                                'CURRENCY' => 'KES',
				                                'DESCRIPTION'=>"Subscription Payment",
				                                'AMOUNT' => number_to_currency($amount),
				                                'DEPOSIT_DATE' => timestamp_to_date(time()),
				                            );
				                            $notification_array[] = array(
					                            'subject'=>'Payment Received',
					                            'message'=>'Your payment of KES '.$amount.' and transaction code '.$payment_transaction->transaction_id.' has been received',
					                            'from_user'=>$invoice->user_id,
					                            'to_user_id'=>$invoice->user_id,
					                        );                                                
					                        $this->ci->notification_manager->create_bulk($notification_array);
						                  	$this->ci->safaricom_m->update_stkpushrequest($payment_transaction->id,$update);
						                  	return TRUE;
						                }else{
						                	return FALSE;
						                }
						                /*if($this->update_invoices($invoice->user_id)){	                    				
			            					if($this->update_user_subscription_status($invoice->user_id,$invoice->subcription_end_date)){
			            						$success++;
							                }else{
							                	$fails++;
							                }
						                }else{
						                	$fails++;
						                }*/

						                /*if($statement_entry_id = $this->ci->statements_m->insert($input)){
						                	
						                }else{
						                	$fails++;
						                }*/
						            }else{
						            	return FALSE;
						            }
		                    	}else{
		                    		//update user arrears 
		                    		return TRUE;
		                    	}
							}else{
								$this->session->set_flashdata('error','Users details could not be updated');
	            				return FALSE;
							}
			            }else{
			              return FALSE;
			            }
			        }else{
			        	return FALSE;
			        }
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

  	public function update_invoices($invoice_id = 0, $amount = 0 ){
        if($invoice_id && $amount){
            $invoice = $this->ci->invoices_m->get($invoice_id,TRUE);
            if($invoice){
                $amount_paid = $amount;
                $invoice_amount_paid = 0;
                $amount_payable = $invoice->amount_payable?:0;
                if($invoice->billing_package_id == 1){
     				$end_date = strtotime("+ 7 days");
     			}else if($invoice->billing_package_id == 2){
     				$end_date = strtotime("+ 30 days");
     			}else if($invoice->billing_package_id == 3){
     				$end_date = strtotime("+ 12 months");
     			}else{
     				$end_date = strtotime("+ 7 days");
     			}
                $amount_payable = $invoice->amount_payable?:0;                
                $input = array(
            		'amount_paid'=>$amount + $invoice->amount_paid,
                	'status'=>'1',
                	//'subcription_end_date'=>$end_date
            	);
            	if($this->ci->invoices_m->update($invoice->id,$input)){
            		return TRUE;
            	}else{
            		return FALSE;
            	}
            }else{
                return TRUE;
            }

        }else{
            $this->session->set_flashdata('error','Invoice id must be passed');
            return FALSE;
        }
    }

    public function update_user_subscription_status($user_id= 0,$invoice_id = 0){
        /***
        	1. User Subscribed
            2. User on ongoing trials
            3. User not subscribed but trial expired
            4. User subscribed but has arrears
            5. User subscribed but has an overpayment
        **/
        if($user_id && $invoice_id){
            $user = $this->ci->users_m->get($user_id);
            if($user){
                $update = array();
                $account_arrears = $this->ci->invoices_m->get_user_account_arrears($user_id);
                if($account_arrears>0){

                	//subscription still active check payments
                	$latest = $this->ci->invoices_m->get($invoice_id);
					$deposits = $this->ci->deposits_m->get_my_active_deposits_per_invoice_id($user_id,$invoice_id);
					$total_paid = 0;
					if($deposits){
						foreach ($deposits as $key => $deposit) {
							$total_paid += $deposit->amount;
						}
					}
					$account_arrears = $latest->amount_payable - $total_paid;
					
					if($total_paid > $latest->amount_payable){
						//overpayment
						$subscription_status = 5;
					}else{
						$subscription_status = 4;
					}
					$update_user = array(
						'arrears'=>$account_arrears,
						'subscription_status'=>$subscription_status,
						'subscription_end_date'=>$latest->subcription_end_date
					);
					$this->ci->users_m->update($user_id,$update_user);
                    $update = array(
                        'subscription_status' => $subscription_status,
                        'modified_on' => time(),
                    );
                }else{
                    $update = array(
                        'subscription_status' =>1,
                        'modified_on' => time(),
                    );
                }
                if($update){
                    $update['arrears'] = $this->ci->invoices_m->get_user_account_arrears($user_id);
                    $this->ci->users_m->update($user->id,$update);
                    return TRUE;
                }
            }else{
                return FALSE;
            }
        }else{
            return FALSE;
        }
    }

    public function check_user_subscription_status($user = array()){
    	/***
        	1. User Subscribed
            2. User on ongoing trials
            3. User not subscribed but trial expired
            4. User subscribed but has arrears
            5. User subscribed but has an overpayment
        **/
    	if($user){
    		$arrears = $this->ci->invoices_m->get_user_account_arrears($user->id);
    		print_r($arrears); die();
    		if($arrears){
    			$response = array(
                    'status'=>1,
                    'is_subscribed' =>1,
                    'on_trial' => 0,
                    'subscription_status'=>$user->subscription_status,
                    'subscription_end_date'=>$user->subscription_end_date,
                    'has_arrears' => 1,
                    'has_not_subscribed' => 0,
                    'arrears' =>$arrears,
                ); 
                return $response;
    		}else{
    			$today = time();
    			if($user->subscription_end_date > $today ){
    				//subscription has not reached the end date
    				$response = array(
                        'status'=>1,
                        'is_subscribed' =>1,
                        'on_trial' => 0,
                        'description'=>'Subscription has not expired',
                        'subscription_status'=>$user->subscription_status,
                        'subscription_end_date'=>$user->subscription_end_date,
                        'has_arrears' => 0,
                        'has_not_subscribed' => 0,
                        'arrears' =>$arrears,
                    ); 
                    return $response;
    			}else{
    				$plus_14_days = strtotime(" +14 days",$user->created_on);
    				if($plus_14_days > $today){
    					//on trial
    					$response = array(
                        'status'=>1,
                        'is_subscribed' =>1,
                        'on_trial' => 0,
                        'description'=>'On trial',
                        'subscription_status'=>$user->subscription_status?$user->subscription_status:4,
                        'subscription_end_date'=>$user->subscription_end_date,
                        'has_arrears' => 0,
                        'has_not_subscribed' => 0,
                        'arrears' =>$user->arrears,
                    ); 
                    return $response;
    				}else{
    					//trial expired generate invoice 
    					$latest = $this->ci->invoices_m->get_latest_invoice($user->id);
    					if($latest){
    						if($latest->subcription_end_date > $today){
    							//subscription still active check payments
    							$deposits = $this->ci->deposits_m->get_my_active_deposits_per_invoice_id($user->id,$latest->id);
    							$total_paid = 0;
    							if($deposits){
    								foreach ($deposits as $key => $deposit) {
    									$total_paid += $deposit->amount;
    								}
    							}
    							$arrears = $latest->amount_payable - $total_paid;
    							$input = array(
    								'amount_paid'=>$total_paid
    							);
    							$this->ci->invoices_m->update($latest->id,$input);
    							if($total_paid > $latest->amount_payable){
    								//overpayment
    								$subscription_status = 5;
    							}else{
    								$subscription_status = 4;
    							}
    							$update_user = array(
    								'arrears'=>$arrears,
    								'subscription_status'=>$subscription_status,
    								'subscription_end_date'=>$latest->subcription_end_date
    							);
    							$this->ci->users_m->update($user->id,$update_user);
    							$response = array(
			                        'status'=>1,
			                        'is_subscribed' =>1,
			                        'on_trial' => 1,
			                        'description'=>'subscription still active',
			                        'subscription_status'=>$subscription_status,
			                        'has_arrears' => 1,
			                        'has_not_subscribed' => 0,
			                        'arrears' =>$arrears,
			                    );
			                    return $response;
    						}else{
    							//subscription has expired generate invoice(subscribe)
    							$package = array(
					                'billing_package_id'=>'',
					                'user'=>$user
					            );
					            if($this->subscribe($package)){
					            	//trial expired generate invoice 
	    							$latest = $this->ci->invoices_m->get_latest_invoice($user->id);
	    							$response = array(
				                        'status'=>1,
				                        'is_subscribed' =>1,
				                        'on_trial' => 0,
				                        'description'=>'1. trial expired generate invoice',
				                        'subscription_status'=> 4,
				                        'has_arrears' => 1,
				                        'has_not_subscribed' => 0,
				                        'arrears' =>$latest->amount_payable,
				                    );
				                    return $response;				                
					            }else{
					                return FALSE;
					            }
    						}
    					}else{
    						$package = array(
				                'billing_package_id'=>'',
				                'user'=>$user
				            );
				            if($this->subscribe($package)){
				            	//trial expired generate invoice 
    							$latest = $this->ci->invoices_m->get_latest_invoice($user->id);
    							$response = array(
			                        'status'=>1,
			                        'is_subscribed' =>1,
			                        'on_trial' => 0,
			                        'description'=>'2. trial expired generate invoice',
			                        'subscription_status'=> 4,
			                        'has_arrears' => 1,
			                        'has_not_subscribed' => 0,
			                        'arrears' =>$latest->amount_payable,
			                    );
			                    return $response;				                
				            }else{
				                return FALSE;
				            }
    					}
    				}
    			}
    		}
    	}else{
    		return FALSE;
    	}
    }
}