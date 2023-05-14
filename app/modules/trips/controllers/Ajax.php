<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

    protected $validation_rules = array(
        array(
            'field' => 'id',
            'label' => 'Trip id',
            'rules' => 'trim|required',
        )
        
    );

    function __construct(){
        parent::__construct();
        $this->load->model('trips_m');
        $this->load->model('schools/schools_m');
        $this->load->model('vehicles/vehicles_m');
        $this->load->model('routes/routes_m');
        $this->load->library('trips_manager');    
    }

    public function start(){
        //$post = new StdClass();
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $trip_id = $this->input->post('id');
            $trip = $this->trips_m->get($trip_id);
            if($trip){
                if($this->trips_manager->start_journey($trip, $this->user->id)){
                    $this->response = array(
                        'status' => 200,
                        'message' => 'Journey Successfully initiated'
                    ); 
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => 'Could not start a journey: '.$this->session->warning,
                    );
                }

            }else{
                $this->response = array(
                    'result_code' => 400,
                    'message' => "Could not get a trip ",
                );
            }
            
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $this->response = array(
                'result_code' => 400,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        }
        echo json_encode($this->response);
    }

    public function end_trip(){
        //$post = new StdClass();
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $journey_id = $this->input->post('id');
            $journey = $this->trips_m->get_active_journey($journey_id);
            if($journey){
                $input = array(
                    'status'=> 2,
                    'modified_on'=> time(),
                    'modified_by'=> $this->user->id
                );
                if($update = $this->trips_m->update_journey($journey_id,$input)){
                    $student_journeys = $this->students_m->get_ongoing_journeys_active_ids_to_finish_journey_details($journey_id);
                    if(count($student_journeys) > 0 ){
                        $push_notification = [];
                        $sms_notification = [];
                        $notifications_array = [];
                        $notifications_array_driver = [];
                        $user_parent_ids = [];
                        $vehicle = $this->vehicles_m->get($journey->vehicle_id);
                        foreach ($student_journeys as $key => $active) {
                            $input_journey = array(
                                'is_journey_end'=> 1,
                                //'is_onborded'=> 2,
                                'on_end_longitude'=>$longitude,
                                'on_end_latitude'=>$latitude,
                                'modified_on'=> time(),
                                'modified_by'=> $user_id
                            );
                            $this->students_m->update_student_journey($active->id,$input_journey);
                        }

                    }

                    $this->response = array(
                        'status' => 200,
                        'message' => 'You have successfully ended the journey'
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => 'Could not get active journey details: '.$this->session->warning,
                    );
                }

            }else{
                $this->response = array(
                    'result_code' => 400,
                    'message' => "Could not get a active journey ",
                );
            }
            
        }else{
            $post = array();
            $form_errors = $this->form_validation->error_array();
            foreach ($form_errors as $key => $value) {
                $post[$key] = $value;
            }
            $this->response = array(
                'result_code' => 400,
                'message' => 'Form validation failed',
                'validation_errors' => $post,
            );
        }
        echo json_encode($this->response);
    }

    public function get_dashboard_stats(){

        $top = $this->trips_m->get_dashboard_stats();
        $vehicle_ids = [];
        foreach ($top as $key => $t) {
            $vehicle_ids[] = $key;
        }
        $vehicle_options = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
        $response_arr = [];
        foreach ($top as $key => $t) {
            if(array_key_exists($key , $vehicle_options)){
                $registration = $vehicle_options[$key];
                $response_arr[] = (object)[
                    'count' => $t,
                    'vehicle'=> $registration
                ];
            }
        }
        $this->response = array(
            'status' => 200,
            'message' => 'Success',
            'data' => $response_arr,
        );
        echo json_encode($this->response);
    }

    public function get_driver_active_trips($driver_id =0){
        $html ='';
        if($driver = $this->users_m->get_user_by_driver_id($driver_id)){
            $vehicle = $this->vehicles_m->get($driver->vehicle_id);
            if($vehicle){
                $active_trips = $this->trips_m->get_driver_vehicle_active_journey($driver_id,$vehicle->id);
                if(count($active_trips) > 0){
                    $html ='<div class="kt-widget4">';
                    $route_ids = [];
                    $trip_ids = [];
                    foreach ($active_trips as $key => $trip) {
                        $route_ids[] = $trip->route_id;
                        $trip_ids[] = $trip->trip_id;
                    }
                    //print_r($active_trips); die();
                    $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                    $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                    $results = [];
                    foreach ($active_trips as $key => $trip) {
                        $from = '';
                        $destination = "";
                        $start_longitude = "";
                        $start_latitude = "";
                        $destination_longitude = "";
                        $destination_latitude = "";
                        $distance = "";
                        $duration = "";
                        $trip_name = '';
                        $trip_time = '';
                        if(array_key_exists($trip->route_id,$routes)){
                            $from = $routes[$trip->route_id]->start_point;
                            $destination =  $routes[$trip->route_id]->end_point;
                            $start_longitude = $routes[$trip->route_id]->start_longitude;
                            $start_latitude = $routes[$trip->route_id]->start_latitude;
                            $destination_longitude = $routes[$trip->route_id]->destination_longitude;
                            $destination_latitude = $routes[$trip->route_id]->destination_latitude;
                            $distance = $routes[$trip->route_id]->distance;
                            $duration = $routes[$trip->route_id]->duration;
                        }
                        if(array_key_exists($trip->trip_id,$trips)){
                            $trip_name = $trips[$trip->trip_id]->name;
                            $trip_time =  $trips[$trip->trip_id]->trip_time;
                        }
                        $html.='<div class="kt-widget4__item">
                                <div class="kt-widget4__info">
                                    <a class="kt-widget4__username">
                                        '.$trip_name.'
                                    </a>
                                    <p class="kt-widget4__text">
                                        '.$from .' - '. $destination .'
                                    </p>
                                    <p class="kt-widget4__text">
                                        ETA <strong>'.$duration .'</strong> Distance <strong>'. $distance .'</strong>
                                    </p>
                                </div>
                                <a  href="'.site_url('/admin/trips/history/'.$trip->id).'" data-id='.$trip->id.' class="btn btn-sm btn-label-success btn-bold">Track</a>
                            </div>';

                        $results[] = array(
                            'trip_id' =>$trip->id,
                            'name'=>$trip_name,
                            'trip_time' =>$trip_time,
                            'from' =>$from,
                            'destination' =>$destination,
                            'start_longitude' =>$start_longitude,
                            'start_latitude' =>$start_latitude,
                            'destination_longitude' =>$destination_longitude,
                            'destination_latitude' =>$destination_latitude,
                            'distance' =>$distance,
                            'duration' =>$duration,
                        );
                    }
                    $response = array(
                        'status' => TRUE,
                        'message' => 'Operation successfully',
                        'data' => $results
                    );
                    $html.='</div>';
                    // /print_r($response);
                }else{
                    $html.='<div class="alert alert-info fade show" role="alert">
                        <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                        <div class="alert-text">There are no ongoing trips!</div>
                    </div>';
                }
            }else{
                $response = array(
                    'status' =>FALSE,
                    'message' => 'Error fetching vehicle details: ',
                );
            }
        }
        echo $html;
    }
}