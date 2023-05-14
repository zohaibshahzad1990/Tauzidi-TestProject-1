<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Schools_manager{
	protected $ci;
	public $settings;
	public $user;

  	public function __construct(){
		$this->ci= & get_instance();
		set_time_limit(0);
        ini_set('memory_limit','2048M');
        ini_set('max_execution_time', 1200);		
    	$this->ci->load->model('schools/schools_m');
		$this->settings = $this->ci->settings_m->get_settings(1)?:'';
  	}


  	public function create_school($school_array = array()){
  		if($school_array){
  			$school = (object)$school_array;
  			if($this->ci->schools_m->get_school_by_slug($school->slug)){
  				$this->ci->session->set_flashdata('warning','A school with a similar name exist');
            	return FALSE;
  			}else{
  				$input = array(
	              	'name'=>$school->name,
	              	'slug'=>$school->slug,
	              	'user_id'=>$school->user_id,
	              	'description'=>$school->description,
	              	'active'=>1,
	              	'created_on'=>time(),
	              	'created_by'=>$school->created_by,
	            );
	            if($school_id = $this->ci->schools_m->insert($input)){
            		return $school_id;	                
	            }else{
	               $this->ci->session->set_flashdata('warning','Could not create school '.$school->name .' try again');
           		   return FALSE;
	            }
  			}
  		}else{
  			$this->ci->session->set_flashdata('warning','School array parameter');
            return FALSE;
  		}
  	}

  	public function update_school($school_array = array()){
  		if($school_array){
  			$school = (object)$school_array;
  			if($school->user_id){
	  				if($this->ci->user = $this->ci->users_m->get($school->user_id)){
	  					if($school->school_id){
	  						if($school_details = $this->ci->schools_m->get($school->school_id)){
				  				$input = array(
					              	'name'=>$school->name,
					              	'slug'=>$school->name,
					              	'user_id'=>$school->user_id,
					              	'description'=>$school->description,
					              	'active'=>1,
					              	'modified_on'=>time(),
					              	'modified_by'=>$this->ci->user->id,
					            );
					            if($result = $this->ci->schools_m->update($school_details->id,$input)){
				            		return true;	                
					            }else{
					               $this->ci->session->set_flashdata('warning','Could not update school '.$school->name .' try again');
				           		   return FALSE;
					            }
					        }else{
					        	$this->ci->session->set_flashdata('warning','School details could not be found');
		            			return FALSE;	
					        }
				         }else{
				        	$this->ci->session->set_flashdata('warning','School id variable is missing in JSON payload');
		            		return FALSE;
				        	}
		        	}else{
		        		$this->ci->session->set_flashdata('warning','User details could not be found');
	            	return FALSE;
		        	}
			   }else{
			    	$this->ci->session->set_flashdata('warning','User id varible is missing in JSON variable');
		            return FALSE;
			   }
  		}else{
  			$this->ci->session->set_flashdata('warning','School array parameter');
            return FALSE;
  		}
  	}

  	public function driver_school($driver_array = array()){
  		if($driver_array){
  			$driver = (object)$driver_array;
  			if($driver->user_id){
	  			if($driver->school_id){
	  				if($school_details = $this->ci->schools_m->get($driver->school_id)){
	  					$update = array(
		  					'user_id'=>$driver->user_id,
	                    	'school_id'=>$driver->school_id,
	                    	'vehicle_id'=>$driver->vehicle_id,
		  				);
			  			if($existing = $this->ci->users_m->get_user_driver_details($driver->user_id)){
			  				$update = $update + array(
			  					'modified_on'=>time(),
			  					'modified_by'=>$driver->user_id,
			  				);
			  				//$this->ci->users_m->update_user_driver_pairing($existing->id,$update);
			  				if($driver_id = $this->ci->users_m->update_user_driver_pairing($existing->id,$update)){
			            		return $driver_id;	                
				            }else{
				               $this->ci->session->set_flashdata('warning','Could not update driver user details try again');
			           		   return FALSE;
				            }
			  			}else{
			  				$update = $update  + array(
		                    	'created_on'=>$driver->created_on,
		                    	'created_by'=>$driver->created_by,
		                   		'active'=>1,
			               	);
			               	if($driver_id = $this->ci->users_m->insert_user_driver_pairing($update)){
			            		return $driver_id;	                
				            }else{
				               $this->ci->session->set_flashdata('warning','Could not create driver user details try again');
			           		   return FALSE;
				            }
			  			}			            
			  		}else{
			  			$this->ci->session->set_flashdata('warning','School details could not be found');
			            return FALSE;
			  		}
		  		}else{
		  			$this->ci->session->set_flashdata('warning','School id variable is missing');
		            return FALSE;
		  		}
			}else{
				$this->ci->session->set_flashdata('warning','User id varible is missing in JSON variable');
		        return FALSE;
			}
  		}else{
  			$this->ci->session->set_flashdata('warning','Driver array parameter empty(var)');
            return FALSE;
  		}
  	}



} ?>