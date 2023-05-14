<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');
	class Billing_manager{
	  	protected $ci;


	  	public function __construct(){
			$this->ci= & get_instance();
		    set_time_limit(0);
		    ini_set('memory_limit','2048M');
		    ini_set('max_execution_time', 1200);
		    //$this->ci->load->model('safaricom/safaricom_m');
		    $this->ci->load->model('transactions/transactions_m');
		    $this->ci->load->model('deposits/deposits_m');
		    $this->ci->load->model('invoices/invoices_m');
		    $this->ci->load->model('users/users_m');
		    $this->ci->load->model('billing/billing_m');
			$this->ci->load->model('notifications/notifications_m');
			$this->ci->load->model('statements/statements_m');
			$this->ci->load->library('Curl');
		}

		function automated_user_billing_invoice($date=0,$limit=0){
	        //runs for 3 hours(12-1-2-3) after every 5 minutes. Each query is only limited to five users to be billed today.
	        if($date){
	        }else{
	            $date = time();
	        }
	        $success = 0;
	        $fails = 0;
	        $limit = 20;
	        $users = $this->ci->users_m->get_users_to_be_billed_today($date,$limit);
	        if($users){
	        	//print_r($users); die();
	            foreach ($users as $user){      	
	                if(date('dmy',$user->billing_date) == date('dmy',$date)){
	                	/*last_login*/
		               	if($user->billing_package_id){
			                $billing = $this->ci->billing_m->get($user->billing_package_id);
			     		}else{
			     			$billing = $this->ci->billing_m->check_if_default();
			     		}
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
			     			$amount_payable = $billing->amount;

			     			$percentage_tax = 0;
		                    if($billing->enable_tax){
		                        $percentage_tax = $billing->percentage_tax;
		                        if($percentage_tax){
		                            $tax = (($percentage_tax/100)*($amount_payable));
		                            $tax = round($tax,2);
		                        }else{
		                            $tax = 0;
		                        }
		                    }else{
		                        $tax = 0;
		                    }
		                    $last_invoice_valid = TRUE;
		                    $last_invoice = $this->ci->invoices_m->get_latest_invoice($user->id);
		                    if($last_invoice){
		                    	if($last_invoice->is_paid){
		                    		$last_invoice_valid = FALSE;
		                    	}
		                    }else{
		                    	$last_invoice_valid = FALSE;
		                    }
		                    if($amount_payable){
		                    	if($last_invoice_valid){
		                    		//has pending unpaid invoice don' generate new invoice
		                    		$update = array(
		                    			'modified_on'=>time(),
		                    			'modified_by'=>1,
		                    		);
		                    		$invoice_input = array(
		                    			'invoice_id'=>$last_invoice->id,
		                    			'created_on'=>time(),
		                    			'created_by'=>1,
		                    			'active'=>1
		                    		);
		                    		$this->update_user_subscription_status($user->id,$end_date,0);
		                    		$this->ci->invoices_m->update($last_invoice->id,$update);
		                    		$this->ci->invoices_m->insert_invoice_to_pay($invoice_input);
		                    		$success++;
		                    	}else{
			                        $input = array(
						                'type'=>1,
						                'invoice_number'=>'INV-'.$this->ci->invoices_m->calculate_billing_invoice_number($user->id),
						                'user_id'=>$user->id,
						                'invoice_date'=>time(),
						                'due_date'=>$end_date,
						                'tax' => $tax,
						                'billing_package_id'=>$billing->id,
						                'subcription_end_date'=>$end_date,
						                'amount_payable'=>ceil(currency($amount_payable+$tax)),
						                'amount_paid'=>0,
						                'description'=>"Subscription Invoice",
						                'active'=>1,
						                'created_on'=>time(),
						            );
						            if($invoice_id = $this->ci->invoices_m->insert($input)){
						                $input = array(
						                    'transaction_type'=>1,
						                    'transaction_date'=>time(),
						                    'user_id'=>$user->id,
						                    'invoice_id'=>$invoice_id,
						                    'amount'=>ceil(currency($amount_payable+$tax)),
						                    'description'=>"Subscription Invoice",
						                    'active'=>1,
						                    'created_on'=>time(),
						                );
						                $invoice_input = array(
			                    			'invoice_id'=>$invoice_id,
			                    			'created_on'=>time(),
			                    			'created_by'=>1,
			                    			'active'=>1
			                    		);
										$this->ci->invoices_m->insert_invoice_to_pay($invoice_input);
						                $total_amount_payable = ceil(currency($amount_payable+$tax));
						                if($statement_entry_id = $this->ci->statements_m->insert($input)){
						                	//$this->update_invoices($user->id);					                	
	                    					$this->update_user_subscription_status($user->id,$end_date,$total_amount_payable);
						                	if($this->ci->transactions->update_user_statement_balances($user->id,time())){
						                		$success++;
						                	}else{
						                		++$fails;
						                	}	                    
						                }else{
						                    //could not insert statement entry
						                    ++$fails;
						                }
						            }else{
						                //could not insert invoice entry
						                ++$fails;
						            }
						        }
		                    }else{
		                        ++$fails;
		                    }
		                }else{
		                	++$fails;	
		                }
	               	}else{
	                    ++$fails;
	               	}
	            }
	        }else{
	            //no users
	        }
	        if($success){
	            echo $success.' users(s) billed successfully on'.date('d-m-Y',$date);
	        }
	        if($fails){
	            echo $fails.' users(s) billing failed on '.date('d-m-Y',$date);
	        }

	        if(!$fails && !$success){
	            echo 'No users to billed on '.date('d-m-Y',$date);
	        }

		}

		function automate_past_user_billing_invoices($date=0,$limit=0){
	        //runs for 3 hours(12-1-2-3) after every 5 minutes. Each query is only limited to five users to be billed today.
	        if($date){
	        }else{
	            $date = time();
	        }
	        $success = 0;
	        $fails = 0;
	        $limit = 20;
	        $users = $this->ci->users_m->get_users_past_billing($date,$limit);

	        if($users){
	            foreach ($users as $user){         	
	                //if(date('dmy',$user->billing_date) == date('dmy',$date)){
	                	/*last_login*/
		               	if($user->billing_package_id){
			                $billing = $this->ci->billing_m->get($user->billing_package_id);
			     		}else{
			     			$billing = $this->ci->billing_m->check_if_default();
			     		}
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
			     			$amount_payable = $billing->amount;

			     			$percentage_tax = 0;
		                    if($billing->enable_tax){
		                        $percentage_tax = $billing->percentage_tax;
		                        if($percentage_tax){
		                            $tax = (($percentage_tax/100)*($amount_payable));
		                            $tax = round($tax,2);
		                        }else{
		                            $tax = 0;
		                        }
		                    }else{
		                        $tax = 0;
		                    }
		                    if($amount_payable){
		                        $input = array(
					                'type'=>1,
					                'invoice_number'=>'INV-'.$this->ci->invoices_m->calculate_billing_invoice_number($user->id),
					                'user_id'=>$user->id,
					                'invoice_date'=>time(),
					                'due_date'=>$end_date,
					                'tax' => $tax,
					                'billing_package_id'=>$billing->id,
					                'subcription_end_date'=>$end_date,
					                'amount_payable'=>ceil(currency($amount_payable+$tax)),
					                'amount_paid'=>0,
					                'description'=>"Subscription Invoice",
					                'active'=>1,
					                'created_on'=>time(),
					            );
					            if($invoice_id = $this->ci->invoices_m->insert($input)){
					                $input = array(
					                    'transaction_type'=>1,
					                    'transaction_date'=>time(),
					                    'user_id'=>$user->id,
					                    'invoice_id'=>$invoice_id,
					                    'amount'=>ceil(currency($amount_payable+$tax)),
					                    'description'=>"Subscription Invoice",
					                    'active'=>1,
					                    'created_on'=>time(),
					                );
					                $total_amount_payable = ceil(currency($amount_payable+$tax));
					                if($statement_entry_id = $this->ci->statements_m->insert($input)){
					                	//$this->update_invoices($user->id);					                	
                    					$this->update_user_subscription_status($user->id,$end_date,$total_amount_payable);
					                	if($this->ci->transactions->update_user_statement_balances($user->id,time())){
					                		$success++;
					                	}else{
					                		++$fails;
					                	}	                    
					                }else{
					                    //could not insert statement entry
					                    ++$fails;
					                }
					            }else{
					                //could not insert invoice entry
					                ++$fails;
					            }
		                    }else{
		                        ++$fails;
		                    }
		                }else{
		                	++$fails;	
		                }
	               	/*}else{
	                    ++$fails;
	               	}*/
	            }
	        }else{
	            //no users
	        }
	        if($success){
	            echo $success.' users(s) billed successfully on'.date('d-m-Y',$date);
	        }
	        if($fails){
	            echo $fails.' users(s) billing failed on '.date('d-m-Y',$date);
	        }

	        if(!$fails && !$success){
	            echo 'No users to billed on '.date('d-m-Y',$date);
	        }

		}

		function _generate_user_billing_invoice($user_id = 0){
			if($user_id){
				$user = $this->ci->users_m->get($user_id);
				if($user){
					if($user->billing_package_id){
		                $billing = $this->ci->billing_m->get($user->billing_package_id);
		     		}else{
		     			$billing = $this->ci->billing_m->check_if_default();
		     		}
		     		if($billing){
		     			if($billing->billing_type_frequency == 1){
		     				$end_date = strtotime("+ 7 days");
		     			}else if($billing->billing_type_frequency == 2){
		     				$end_date = strtotime("+ 30 days");
		     			}else if($billing->billing_type_frequency == 3){
		     				$end_date = strtotime("+ 12 months");
		     			}else{
		     				$end_date = strtotime("+ 7 days",$user->billing_date);
		     			}
		     			$amount_payable = $billing->amount;

		     			$percentage_tax = 0;
	                    if($billing->enable_tax){
	                        $percentage_tax = $billing->percentage_tax;
	                        if($percentage_tax){
	                            $tax = (($percentage_tax/100)*($amount_payable));
	                            $tax = round($tax,2);
	                        }else{
	                            $tax = 0;
	                        }
	                    }else{
	                        $tax = 0;
	                    }
	                    if($amount_payable){
	                        $input = array(
				                'type'=>1,
				                'invoice_number'=>'INV-'.$this->ci->invoices_m->calculate_billing_invoice_number($user->id),
				                'user_id'=>$user->id,
				                'invoice_date'=>time(),
				                'due_date'=>$end_date,
				                'tax' => $tax,
				                'billing_package_id'=>$billing->id,
				                'subcription_end_date'=>$end_date,
				                'amount_payable'=>ceil(currency($amount_payable+$tax)),
				                'amount_paid'=>0,
				                'description'=>"Subscription Invoice",
				                'active'=>1,
				                'created_on'=>time(),
				            );
				            if($invoice_id = $this->ci->invoices_m->insert($input)){
				                $input = array(
				                    'transaction_type'=>1,
				                    'transaction_date'=>time(),
				                    'user_id'=>$user->id,
				                    'invoice_id'=>$invoice_id,
				                    'amount'=>ceil(currency($amount_payable+$tax)),
				                    'description'=>"Subscription Invoice",
				                    'active'=>1,
				                    'created_on'=>time(),
				                );
				                $total_amount_payable = ceil(currency($amount_payable+$tax));
				                if($statement_entry_id = $this->ci->statements_m->insert($input)){
				                	//$this->update_invoices($user->id);					                	
                					$this->update_user_subscription_status($user->id,$end_date,$total_amount_payable);
				                	if($this->ci->transactions->update_user_statement_balances($user->id,time())){
				                		return TRUE;
				                	}else{
				                		return FALSE;
				                	}	                    
				                }else{
				                    //could not insert statement entry
				                    return FALSE;
				                }
				            }else{
				                //could not insert invoice entry
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

		function _reduce_account_balance($user_id=0,$amount,$invoice_id){
			if($user_id){
				$user = $this->ci->users_m->get($user_id);
				if($user){
					if($invoice_id){
						$invoice = $this->ci->invoices_m->get($invoice_id);
						if($invoice){
							$new_balance = $user->arrears - $invoice->amount_paid;
							if($new_balance > 0){
								$subscription_status = 4;
							}else if($new_balance < 0){
								$subscription_status = 5;
							}else{
								$subscription_status = 4;
							}
							$input  = array(
								'subscription_status'=>$subscription_status,
								'arrears'=>$new_balance,
								'subscription_end_date'=>$invoice->subcription_end_date,
							);
							if($this->ci->users_m->update($user->id,$input)){
								return TRUE;
							}else{
								$this->session->set_flashdata('error','Users details could not be updated');
	            				return FALSE;
							}
						}else{
							$this->session->set_flashdata('error','Invoice details missing');
	            			return FALSE;	
						}
					}else{
						$this->session->set_flashdata('error','Invoice id is required');
	            		return FALSE;	
					}
				}else{
					$this->session->set_flashdata('error','user details is missing be passed');
	            	return FALSE;
				}
			}else{
				$this->session->set_flashdata('error','user id must be passed');
	            return FALSE;
			}
		}

		function update_invoices($user_id=0){
	        if($user_id){
	            $invoices = $this->ci->invoices_m->get_user_invoices($user_id,TRUE);
	            $total_amount_paid = $this->ci->billing_m->get_user_billing_paid_amount($user_id);
	            if($invoices){
	                $amount = 0;
	                $amount_paid = $total_amount_paid?:0;
	                $invoice_amount_paid = 0;
	                foreach ($invoices as $invoice){
	                    $amount_payable = $invoice->amount_payable?:0;
	                    if($amount_payable<=$amount_paid){
	                        if($this->ci->invoices_m->update($invoice->id,array('amount_paid'=>$amount_payable,'status'=>'1'))){   
	                            $amount_paid-=$amount_payable; 
	                        }
	                    }else if($amount_paid>0&&$amount_paid<$amount_payable){
	                        if($this->ci->invoices_m->update($invoice->id,array('amount_paid'=>$amount_paid,'status'=>NULL))){  
	                            $amount_paid = 0;
	                        }
	                    }else{
	                        if($this->ci->invoices_m->update($invoice->id,array('amount_paid'=>0,'status'=>NULL))){
	                            continue;
	                        }
	                    }
	                }
	                return TRUE;
	            }else{
	                return TRUE;
	            }

	        }else{
	            $this->session->set_flashdata('error','user id must be passed');
	            return FALSE;
	        }
	    }

	    function update_user_subscription_status($user_id=0, $end_date = 0,$amount_payable =0){
	        /***
	        	1. User Subscribed
	            2. User on ongoing trials
	            3. User not subscribed but trial expired
	            4. User subscribed but has arrears
	            5. User subscribed but has an overpayment
	        **/
	        if($user_id){
	            $user = $this->ci->users_m->get($user_id);
	            if($user){
	                $update = array();
	                $account_arrears = $this->ci->invoices_m->get_user_account_arrears($user_id);
	                $new_arrears = $user->arrears + $amount_payable;
	                if($new_arrears > 0 ){
						$subscription_status = 4;
						$update_user = array(
							'subscription_status'=>$subscription_status,
							'subscription_end_date'=>$end_date,
							'billing_date'=>$end_date,
							'arrears'=>$new_arrears,
						);
						$this->ci->users_m->update($user_id,$update_user);
						return TRUE;
	                }else{
	                    $update = array(
	                        'subscription_status' =>1,
	                        'subscription_end_date'=>$end_date,
	                        'billing_date'=>$end_date,
	                        'arrears'=>$new_arrears,
	                        'modified_on' => time(),
	                    );
	                }
	                if($update){
	                    //$update['arrears'] = $this->ci->invoices_m->get_user_account_arrears($user_id);
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

    	function pay_invoices_from_overpayments($date = 0 , $limit= 0){
    		if($date){
	        }else{
	            $date = time();
	        }
	        $success = 0;
	        $fails = 0;
	        $success = 0;
	        $fails = 0;
	        $limit = 20;
	        $invoices = $this->ci->invoices_m->get_invoices_to_pay_generated_today($date,$limit);
	        if($invoices){	        	
	        	foreach ($invoices as $key => $pay_invoice) {	        		
	        		$invoice = $this->ci->invoices_m->get($pay_invoice->invoice_id);
	        		$user = $this->ci->users_m->get($invoice->user_id);
	        		//$total_deposits = $this->ci->deposits_m->get_user_total_deposits_amount($invoice->user_id);
	        		//$total_bill_payment = $this->ci->billing_m->get_user_billing_paid_amount($invoice->user_id);
	        		//$total_bill_payable = $this->ci->invoices_m->get_user_subscription_amount_payable($invoice->user_id);

	        		//$//arrears = $total_deposits - $total_bill_payment;

	        		/*print_r($arrears); 
	        		echo  "<br>";
	        		print_r($total_deposits); 
	        		echo  " total deposits<br>";
	        		print_r($total_bill_payment); 
	        		echo  "<br>";
	        		print_r($total_bill_payable); die();

	        		print_r($user); die();*/
	        		//print_r($user); die();
	        		$arrears = $user->arrears;
	        		//NB negative arears is an overpayment 
	        		if($arrears < 0){
	        			$amount_to_pay = 0;
	        			if($arrears >= $invoice->amount_payable){
	        				$amount_to_pay = $invoice->amount_payable;
	        			}else if($arrears <= $invoice->amount_payable){
	        				$amount_to_pay = $arrears;
	        			}
	        			$payments_array = array(
	        				'user_id' => $invoice->user_id,
			                'receipt_date' => $date,
			                'billing_receipt_number' => 'RCPT-'.$this->ci->billing_m->calculate_billing_receipt_number($invoice->user_id),
			                'amount' => $amount_to_pay,
			                'payment_method'=> 1,
			                'active'=>1,
			                'billing_package_id'=>$invoice->billing_package_id,
			                'description' =>"Auto Pay from subscription Payment overpayment",
			                'transaction_code' => "Auto Pay",
			                'created_on' => time(),
			                'billing_invoice_id'=> $invoice->id,
	        			);

	        			$bill_payment_id = $this->ci->billing_m->insert_billing_payments($payments_array);
	        			if($bill_payment_id){
	        				if($amount_to_pay === $invoice->amount_payable){
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
    						$this->ci->invoices_m->update($invoice->id,$update);
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
			                if($this->_reduce_account_balance($user->id,$amount_to_pay,$invoice->id)){
			                	$this->ci->invoices_m->delete_invoice_to_pay($pay_invoice->id);
			                	$success++;
			                }else{
			                	$fails++;
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
			            	$fails++;
			            }
	        		}else{
	        			$update = array(
							'amount_paid'=>0,
							'is_auto_pay'=>2,
							'modified_on'=>time(),
							'modified_by'=>1
						);
						if($this->ci->invoices_m->update($invoice->id,$update)){
							$this->ci->invoices_m->delete_invoice_to_pay($pay_invoice->id);
	                  		$success++;
	                  	}else{
	                  		$fails++;	
	                  	}
	        		}
	        	}
	        }else{
	        	echo "No invoice to pay";
	        }
    	}

	    function fix_user_billing_arrears($limit = 0){
	    	$user_totals = $this->ci->deposits_m->get_total_deposits_per_user();
	    	$users = $this->ci->users_m->get_all_users();
	    	//$input = array();
	    	$success = 0;
			$fails = 0;
	    	foreach ($users as $key => $user) {
	    		$input = array(
	    			'arrears'=> isset($user_totals[$user->id])?'-'.$user_totals[$user->id]:0,
	    		);
	    		if($this->ci->users_m->update($user->id,$input)){
	    			$success++;
	    		}else{
	    			$fails++;
	    		}
	    	}

	    	echo $success ." success<br>";
	    	echo $fails ." fails<br>";
	    	print_r($input); 
	    	print_r($user_totals);die();
	    }

        
}