<?php 
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Trips_manager{
	protected $ci;
	public $settings;
	public $user;

  	public function __construct(){
		$this->ci= & get_instance();
		set_time_limit(0);
        ini_set('memory_limit','2048M');
        ini_set('max_execution_time', 1200);		
    	$this->ci->load->model('schools/schools_m');
    	$this->ci->load->model('vehicles/vehicles_m');
    	$this->ci->load->model('students/students_m');
    	$this->ci->load->model('routes/routes_m');
    	$this->ci->load->model('trips/trips_m');
    	$this->ci->load->model('parents/parents_m');
		$this->settings = $this->ci->settings_m->get_settings(1)?:'';
  	}

  	public function start_journey($trip = "",$user_id = 0){
  		if($trip){
  			if($route = $this->ci->routes_m->get($trip->route_id)){
  				
  				if($driver = $this->ci->users_m->get_user_driver($user_id)){
  					if($vehicle = $this->ci->vehicles_m->get_vehicle_school_by_id($driver->vehicle_id)){
  						$check = $this->ci->trips_m->get_driver_vehicle_active_journey($driver->driver_id,$vehicle->id);
  						if($check){
  							$this->ci->session->set_flashdata('warning','You have a similar ongoing journey');
			           		return FALSE;
  						}else{
			  				$input = array(
				              	'trip_id'=>$trip->id,
				              	'vehicle_id'=>$vehicle->id,
				              	'driver_id'=>$driver->driver_id,
				              	'route_id'=>$route->id,
				              	'school_id'=>$trip->school_id,
				              	'start_longitude'=>$route->start_longitude,
								'start_latitude'=>$route->start_latitude,
								'destination_longitude'=>$route->destination_longitude,
								'destination_latitude'=>$route->destination_latitude,
								'distance'=>$route->distance,
								'on_start_longitude'=>$trip->on_start_longitude,
								'on_start_latitude'=>$trip->on_start_latitude,
				              	'status'=>1,
				              	'tentative_start_time'=>$trip->trip_time,
				              	'start_time'=>time(),
				              	'active'=>1,
				              	'created_on'=>time(),
				              	'created_by'=>$user_id,
				            );
			  				if($journey_id = $this->ci->trips_m->insert_journey($input)){
			  					$students = $this->ci->students_m->get_student_details_by_vehicle_id($vehicle->id);
			  					if($students){
			  						$student_arr = [];
			  						$push_notification = [];
			  						$sms_notification = [];
			  						$notifications_array = [];
			  						$notifications_array_driver = [];
			  						$user_parent_ids = [];
			  						foreach ($students as $key => $student) {
			  							$user_parent_ids[] = $student->user_parent_id;
			  						}
			  						$parents = $this->ci->parents_m->get_parent_full_options_by_user_ids($user_parent_ids);
			  						foreach ($students as $key => $student) {
			  							$student_arr[] = array(
			  								'journey_id'=>$journey_id,
			  								'student_id' => $student->id,
							              	'trip_id'=>$trip->id,
							              	'vehicle_id'=>$vehicle->id,
							              	'driver_id'=>$driver->driver_id,
							              	'route_id'=>$route->id,
							              	'school_id'=>$trip->school_id,
							              	'start_longitude'=>$route->start_longitude,
											'start_latitude'=>$route->start_latitude,
											'destination_longitude'=>$route->destination_longitude,
											'destination_latitude'=>$route->destination_latitude,
											'distance'=>$route->distance,
							              	'is_onborded'=>0,
							              	'is_journey_end'=>0,
							              	'active'=>1,
							              	'created_on'=>time(),
							              	'created_by'=>$user_id,
							            );

							            $message = $this->ci->sms_m->build_sms_message('journey-start',array(
											'REGISTRATION' => $vehicle->registration
										));
										
										$fcm_token = '';
										$phone = '';
										if(array_key_exists($student->user_parent_id, $parents)){
											$fcm_token = $parents[$student->user_parent_id]->fcm_token;
											$phone = $parents[$student->user_parent_id]->phone;
										}				
										$push_notification[$student->user_parent_id] = array(
											'is_push' =>1,
											'fcm_token' =>$fcm_token,
											'user_id'=>$student->user_parent_id,
											'message'=>$message,
											'created_on'=>time(),
											'created_by'=>$user_id
										);

										$sms_notification[$student->user_parent_id] = array(
											'is_push' =>0,
											'fcm_token' =>$fcm_token,
											'user_id'=>$student->user_parent_id,
											'sms_to' => $phone,
											'message'=>$message,
											'created_on'=>time(),
											'created_by'=>$user_id
										);

										$notifications_array[$student->user_parent_id] = array(
				                            'subject'=>'Trip Started',
				                            'message'=> "Hi ".$student->first_name.", your vehicle ".$vehicle->registration." is en route (".$route->name.") and will arrive shortly.",
				                            "from_user"=> $driver->id,
				                            "to_user_id"=> $student->user_parent_id,
				                            "resource_id"=>$route->id,
				                        );                       
				                        
			  						}
			  						//print_r($push_notification); 
			  						//print_r($sms_notification); die();
			  						if(count($student_arr) > 0){
			  							$this->ci->students_m->insert_batch_student_journies($student_arr);
			  						}

			  						if(count($push_notification) > 0){
			  							$this->ci->sms_m->insert_sms_queue_batch($push_notification);
			  						}
			  						if(count($notifications_array) > 0){
			  							$this->ci->notification_manager->create_bulk($notifications_array);
			  						}
			  						if(count($sms_notification)){
										$this->ci->sms_m->insert_sms_queue_batch($sms_notification);
			  						}

			  					}
			            		return $journey_id;	                
				            }else{
				               $this->ci->session->set_flashdata('warning','Could not start journey school, try again');
			           		   return FALSE;
				            }
				        }
		  			}else{
		  				$this->ci->session->set_flashdata('warning','Could not get vehicle details try again');
           				return FALSE;
		  			}
	  			}else{
	  				$this->ci->session->set_flashdata('warning','Could not get driver details try again');
           			return FALSE;
	  			}
  			}else{
  				$this->ci->session->set_flashdata('warning','Could not get route details try again');
           		return FALSE;
  			}
  		}else{
  			$this->ci->session->set_flashdata('warning','Trip array parameter');
            return FALSE;
  		}
  	}

  	public function journey_cordinates($trip = ""){
  		if($trip){
  			$driver = $this->ci->users_m->get_user_driver($trip->user_id);
  			if($driver){
	  			if($journey = $this->ci->trips_m->get_active_journey_driver($trip->journey_id,$driver->driver_id)){
		  			if($route = $this->ci->routes_m->get($journey->route_id)){
		  				if($vehicle = $this->ci->vehicles_m->get($journey->vehicle_id)){
			  				$input = array(
				              	'journey_id'=>$trip->journey_id,
								'trip_id'=>$journey->trip_id,
								'vehicle_id'=>$journey->vehicle_id,
								'driver_id'=>$journey->driver_id,
								'route_id'=>$journey->route_id,
								'school_id'=>$journey->school_id,
								'longitude'=>$trip->longitude,
								'latitude'=>$trip->latitude,
								'distance' => $trip->distance,
				                'distance_value' => $trip->distance_value,
				                'duration' => $trip->duration,
				                'heading' => $trip->heading,
				                'speed' => $trip->speed,
				                'speed_accuracy' => $trip->speed_accuracy,
				                'vertical_accuracy' => $trip->speed_accuracy,
				                'duration_value' => $trip->duration_value,
				              	'active'=>1,
				              	'created_on'=>time(),
				              	'created_by'=>$trip->user_id,
				            );
			  				if($journey_id = $this->ci->trips_m->insert_journey_cordinates($input)){
			            		return $journey_id;	                
				            }else{
				               $this->ci->session->set_flashdata('warning','Could not start journey school, try again');
			           		   return FALSE;
				            }
			  			}else{
			  				$this->ci->session->set_flashdata('warning','Could not get vehcile details try again');
		           			return FALSE;
			  			}
		  			}else{
		  				$this->ci->session->set_flashdata('warning','Could not get route details try again');
		           		return FALSE;
		  			}
		  		}else{
					$this->ci->session->set_flashdata('warning','Journey details missing');
            		return FALSE;
		  		}
		  	}else{
		  		$this->ci->session->set_flashdata('warning','Driver details missing');
            	return FALSE;
		  	}
  		}else{
  			$this->ci->session->set_flashdata('warning','Trip array parameter');
            return FALSE;
  		}
  	}
}