<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Student_manager{
	protected $ci;
	public $settings;
	public $user;

  	public function __construct(){
		$this->ci= & get_instance();		
    	$this->ci->load->model('schools/schools_m');
    	$this->ci->load->model('teachers/teachers_m');
    	$this->ci->load->model('students/students_m');
    	$this->ci->load->library('notification_manager');
		$this->settings = $this->ci->settings_m->get_settings(1)?:'';
  	}

  	public function onboard_students($students_array = array()){
  		if($students_array){
  			$students_object = (object)$students_array;
            if($students_object->teacher_id){  
            	$teacher_details = $this->ci->users_m->get($students_object->teacher_id);
            	$class_details = $this->ci->schools_m->get_class($students_object->class_id);          	
            	$emails_array =array();	
            	$invite_array = array();
            	$notification_array = array();
  				foreach ($students_object->students as $key => $student) {
  					$full_names =explode(' ', $student->name);
		            if(count($full_names) > 1){
		                $count = count($full_names);
		                if($count == 2){
		                    $first_name = $full_names[0];
		                    $last_name = $full_names[1];
		                }else if($count == 3){
		                    $first_name = $full_names[0];
		                    $last_name = $full_names[1].' '.$full_names[2];
		                }else if($count == 4){
		                    $first_name = $full_names[0];
		                    $last_name = $full_names[1].' '.$full_names[2].' '.$full_names[3];
		                }		                
		                if($first_name&&$last_name){                    
		                    $group_id = $this->ci->ion_auth->get_group_by_name('Student');
		                    if($group_id){
		                        $groups = array($group_id->id);
		                    }else{
		                        $groups = array(1);
		                    }
		                    $confirmation_code = rand(1000,9999);
		                    $additional_data = array( 
		                        'created_on' => time(),
		                        'first_name' => $first_name, 
		                        'last_name'  => $last_name,
		                        'is_validated'=>0,
		                        'otp_expiry_time'=>strtotime('+24 hours', time()),
		                        'refferal_code' =>generate_random_string(),                      
		                        'confirmation_code'=>$confirmation_code
		                    );
		                    $join_code = generate_join_code();
		                    $email = strtolower($student->email);
		                    if(!valid_email($email)){
		                    	$this->ci->session->set_flashdata('warning','Email entered is invalid');
		                    	return FALSE;
			                    break;
		                    }else{
			                    $user_id = $this->ci->ion_auth->register('',$email,$email, $additional_data,$groups,TRUE);	                    
			                    if($user_id){ 
			                    	$user = $this->ci->users_m->get_user_by_email($email);
			                    	$already_invited = $this->ci->teachers_m->check_if_student_invited($user_id,$students_object->teacher_id); 
			                    	if($already_invited){

			                    	}else{
			                    		$notification_array[] = array(
			                    			'first_name'=>$first_name,
			                    			'email'=>$email,
			                    			'user_id'=>$user_id,
			                    			'invite_code'=>$join_code,
			                    			'class_name'=>$class_details->name,
			                    			'teacher_name'=>$teacher_details->first_name,
			                    		);
				                        $invite_array[] = array(
				                            'invite_code'=>$join_code,
				                            'user_id'=>$user_id,
				                            'class_id'=>$students_object->class_id,
				                            'school_id'=>$students_object->school_id,
				                            'teacher_id'=>$students_object->teacher_id,
				                            'is_accepted'=>0,
				                            'active'=>1,
				                            'modified_on'=>time(),
				                            'modified_by'=>time()
				                        ); 
				                    }
			                    }else{
			                        $user = $this->ci->users_m->get_user_by_email($email);
			                        if($user){
			                        	$already_invited = $this->ci->teachers_m->check_if_student_invited($user->id,$students_object->teacher_id);
			                        	if($already_invited){
				                    	}else{
				                    		$notification_array[] = array(
				                    			'first_name'=>$user->first_name,
				                    			'email'=>$email,
				                    			'user_id'=>$user->id,
				                    			'invite_code'=>$join_code,
				                    			'class_name'=>$class_details->name,
				                    			'teacher_name'=>$teacher_details->first_name,
				                    		);
				                            $invite_array[] = array(
					                            'invite_code'=>$join_code,
					                            'user_id'=>$user->id,
					                            'class_id'=>$students_object->class_id,
					                            'school_id'=>$students_object->school_id,
					                            'teacher_id'=>$students_object->teacher_id,
					                            'is_accepted'=>0,
					                            'active'=>1,
					                            'modified_on'=>time(),
					                            'modified_by'=>time()
					                        );
				                        }
			                        }else{
			                        	$this->ci->session->set_flashdata('warning','User details not found');
		                				return FALSE;
			                        }
			                    }
			                }

		                }else{
		                	$this->ci->session->set_flashdata('warning','User Full Name is invalid');
		                	return FALSE;
		                }
		            }else{
		            	$this->ci->session->set_flashdata('warning','Please Enter Atleast Two Names');
		                return FALSE;
		            }
  				}
  				if($invite_array){
  					//print_r($notification_array); die();
  					if($this->ci->teachers_m->insert_batch($invite_array)){
  						$this->ci->messaging_manager->queue_invite_email($notification_array);
  						$this->ci->session->set_flashdata('warning','Invitations sent successfully');
	        			return TRUE;
  					}else{
  						$this->ci->session->set_flashdata('warning','Invitations could not be sent');
	        			return FALSE;
  					}
  				}else{
  					$this->ci->session->set_flashdata('warning','Could not send invitations');
	        		return FALSE;	
  				}
  			}else{
  				$this->ci->session->set_flashdata('warning','Teacher id varible is missing in JSON variable');
	        	return FALSE;
  			}
  		}else{
	    	$this->ci->session->set_flashdata('warning','Students array parameter empty(var)');
            return FALSE;
	    }
  	}

  	public function read_resource($resource_array  = array()){
  		if($resource_array){
  			$resource_object = (object)$resource_array;
            if($resource_object->resource_id){  
            	$check_if_exist = $this->ci->students_m->is_resource_in_read_mode($resource_object->user_id,$resource_object->resource_id);
            	if($check_if_exist){
            		$input = array(
	                    'active'=>1,
	                    'is_complete'=>0,
	                    'created_by'=>$resource_object->user_id,
	                    'created_on'=>time(),
	                );
	                if($this->ci->students_m->update($check_if_exist->id,$input)){
            			//$this->ci->session->set_flashdata('warning','Resource already in read mode');
	        			return TRUE;
	        		}else{
	        			$this->ci->session->set_flashdata('warning','Resource user pairing could not be updated');
            			return FALSE;
	        		}
            	}else{
            		$input = array(
	                    'school_id'=>$resource_object->school_id,
	                    'class_id'=>$resource_object->class_id,
	                    'teacher_id'=>$resource_object->teacher_id,
	                    'subject_id'=>$resource_object->subject_id,
	                    'resource_id'=>$resource_object->resource_id,
	                    'user_id'=>$resource_object->user_id,
	                    'active'=>1,
	                    'is_complete'=>0,
	                    'created_by'=>$resource_object->user_id,
	                    'created_on'=>time(),
	                );
	                if($this->ci->students_m->insert($input)){
	                	$notifications_array[] = array(
                            'subject'=>'Read Resource',
                            'message'=> "Your started to read a resource ".$resource_object->name." ",
                            "from_user"=> $this->ci->user->id,
                            "to_user_id"=> $this->ci->user->id,
                            "resource_id"=>$resource_object->resource_id,
                        );                        
                        $this->ci->notification_manager->create_bulk($notifications_array);
                        return TRUE;
	                }else{
	                	$this->ci->session->set_flashdata('warning','Resource user pairing could not be created');
            			return FALSE;
	                }
            	}
  			}else{
  				$this->ci->session->set_flashdata('warning','Resource id varible is missing in JSON variable');
	        	return FALSE;
  			}
  		}else{
	    	$this->ci->session->set_flashdata('warning','Resource array parameter empty(var)');
            return FALSE;
	    }
  	}

  	public function save_resource($resource_array  = array()){
  		if($resource_array){
  			$resource_object = (object)$resource_array;
            if($resource_object->resource_id){  
            	$check_if_exist = $this->ci->students_m->is_resource_in_read_mode($resource_object->user_id,$resource_object->resource_id);
            	//print_r($resource_object->user_id);
            	//print_r($check_if_exist); die();
            	if($check_if_exist){
            		$input = array(
	                    'active'=>1,
	                    'is_complete'=>0,
	                    'created_by'=>$resource_object->user_id,
	                    'created_on'=>time(),
	                );
	                if($this->ci->students_m->update_saved_resource($check_if_exist->id,$input)){
            			//$this->ci->session->set_flashdata('warning','Resource already in read mode');
	        			return TRUE;
	        		}else{
	        			$this->ci->session->set_flashdata('warning','Resource could not be saved not be updated');
            			return FALSE;
	        		}
            	}else{
            		$input = array(
	                    'school_id'=>$resource_object->school_id,
	                    'class_id'=>$resource_object->class_id,
	                    'teacher_id'=>$resource_object->teacher_id,
	                    'subject_id'=>$resource_object->subject_id,
	                    'resource_id'=>$resource_object->resource_id,
	                    'user_id'=>$resource_object->user_id,
	                    'active'=>1,
	                    'is_complete'=>0,
	                    'created_by'=>$resource_object->user_id,
	                    'created_on'=>time(),
	                );
	                if($this->ci->students_m->insert_save_resource($input)){
	                	$notifications_array[] = array(
                            'subject'=>'Saved a resource',
                            'message'=> "Your saved a resource ".$resource_object->name." ",
                            "from_user"=> $this->ci->user->id,
                            "to_user_id"=> $this->ci->user->id,
                            "resource_id"=>$resource_object->resource_id,
                        );                        
                        $this->ci->notification_manager->create_bulk($notifications_array);
                        return TRUE;
	                }else{
	                	$this->ci->session->set_flashdata('warning','Save resource could not be created');
            			return FALSE;
	                }
            	}
  			}else{
  				$this->ci->session->set_flashdata('warning','Resource id varible is missing in JSON variable');
	        	return FALSE;
  			}
  		}else{
	    	$this->ci->session->set_flashdata('warning','Resource array parameter empty(var)');
            return FALSE;
	    }
  	}



} ?>