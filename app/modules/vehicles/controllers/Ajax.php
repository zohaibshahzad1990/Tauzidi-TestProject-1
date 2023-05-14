<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

     protected $validation_rules = array(
        array(
            'field' => 'id',
            'label' => 'School Id',
            'rules' => 'trim|required',
        )
    );

    protected $validation_trip_rules = array(
        array(
            'field' => 'trip_id',
            'label' => 'Choose trip',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'vehicle_id',
            'label' => 'vehicle id details',
            'rules' => 'trim|required|callback__check_if_trip_assigned',
        )
        
    );

    protected $validation_type_rules = array(
        array(
            'field' => 'type',
            'label' => 'vehicle type',
            'rules' => 'trim|required|callback__check_if_type_exist',
        ),
        array(
            'field' => 'capacity',
            'label' => 'Vehicle capacity',
            'rules' => 'trim',
        )
        
    );

    function __construct(){
        parent::__construct();
        $this->load->model('vehicles_m');  
        $this->load->model('trips/trips_m');  
        $this->load->model('routes/routes_m');  
    }

    function get_vehicle_per_school(){
        
        $this->form_validation->set_rules($this->validation_rules);
        //$id = $this->post->input('id');
        if($this->form_validation->run()){
            $school_id = $this->input->post('id');
            $vehicles = $this->vehicles_m->get_vehicle_school_by_school_id($school_id);
            $vehicle_arr = [];
            foreach($vehicles as $result){
                $vehicle_arr[] = array(
                    'id'=> $result->id,
                    'name' => $result->registration
                );
            }
            
            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $vehicle_arr
            );
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

    function get_assigned_trip(){
        
        $this->form_validation->set_rules($this->validation_rules);
        //$id = $this->post->input('id');
        if($this->form_validation->run()){
            $vehicle_assign_id = $this->input->post('id');
            $vehicle_trip = $this->vehicles_m->get_assigned_trip($vehicle_assign_id);
            
            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $vehicle_trip
            );
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

    function get_vehicle_type(){
        
        $validation_rules = array(
            array(
                'field' => 'id',
                'label' => 'Vehicle type id',
                'rules' => 'trim|required',
            )
        );
        $this->form_validation->set_rules($validation_rules);
        //$id = $this->post->input('id');
        if($this->form_validation->run()){
            $type_id = $this->input->post('id');
            $type = $this->vehicles_m->get_vehicle_type($type_id);
            
            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $type
            );
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

    function delete_assisgned_trip(){
        $this->form_validation->set_rules($this->validation_rules);
        //$id = $this->post->input('id');
        if($this->form_validation->run()){
            $vehicle_assign_id = $this->input->post('id');
            $vehicle_trip = $this->vehicles_m->get_assigned_trip($vehicle_assign_id);
            if($vehicle_trip){
                $input = array(
                    'active' => 0,
                    'modified_on' => time(),
                    'modified_by' => $this->user->id,
                );
                if($this->vehicles_m->update_vehicle_trips($vehicle_trip->id,$input)){
                    $this->response = array(
                        'result_code' => 200,
                        'message' => 'Successful deleted'
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => 'Could not delete assigned trip',
                    );
                }
                
            }else{
                $this->response = array(
                    'result_code' => 400,
                    'message' => 'Could not get trip assigned',
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

    function assisgn_trip(){
        $this->form_validation->set_rules($this->validation_trip_rules);
        if($this->form_validation->run()){
            $trip_id = $this->input->post('trip_id');
            $vehicle_id = $this->input->post('vehicle_id');
            $vehicle_assigned_id = $this->input->post('vehicle_assigned_id');
            $trip = $this->trips_m->get($trip_id);
            if($trip){
                $vehicle = $this->vehicles_m->get($vehicle_id);
                if($vehicle){

                    $input = array(
                        'trip_id'=> $trip_id,
                        'vehicle_id'  =>$vehicle_id, 
                        'route_id'=>$trip->route_id,
                        'active'=> 1,
                    );
                    $exist = $this->vehicles_m->get_assigned_trip($vehicle_assigned_id);
                    if($exist){
                        $input = $input + array(
                            'modified_on'   => time(),
                            'modified_by'   => $this->user->id,
                        );
                        if($update = $this->vehicles_m->update_vehicle_trips($exist->id,$input)){
                            $this->response = array(
                                'status' => 200,
                                'data' => $update,
                                'message' => 'Trip Successfully  updated '
                            ); 
                        }else{
                           $this->response = array(
                                'result_code' => 400,
                                'message' => "Could not update a vehicle trip ",
                            ); 
                        }
                    }else{
                        $input = $input + array(
                            'created_on'   => time(),
                            'created_by'   => $this->user->id,
                        );
                        if($data = $this->vehicles_m->insert_trips($input)){
                            $this->response = array(
                                'status' => 200,
                                'data' => $data,
                                'message' => 'Trip assigned Successfully to vehicle '.$vehicle->registration
                            ); 
                        }else{
                           $this->response = array(
                                'result_code' => 400,
                                'message' => "Could not assign a vehicle ",
                            ); 
                        }
                    }
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not get vehicle details ",
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

    function type(){
        $this->form_validation->set_rules($this->validation_type_rules);
        if($this->form_validation->run()){
            $type_id = $this->input->post('type_id');
            $name = $this->input->post('type');
            $capacity = $this->input->post('capacity');

            $input = array(
                'name'=> $name,
                'slug'=> generate_slug($name),
                'capacity'  =>$capacity, 
                'active'=> 1,
            );
            $exist = $this->vehicles_m->get_vehicle_type($type_id);
            if($exist){
                $input = $input + array(
                    'modified_on'   => time(),
                    'modified_by'   => $this->user->id,
                );
                if($update = $this->vehicles_m->update_type($exist->id,$input)){
                    $this->response = array(
                        'status' => 200,
                        'data' => $update,
                        'message' => 'Vehicle Type Successfully  updated '
                    ); 
                }else{
                   $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not update a vehicle type ",
                    ); 
                }
            }else{
                $input = $input + array(
                    'created_on'   => time(),
                    'created_by'   => $this->user->id,
                );
                if($data = $this->vehicles_m->insert_types($input)){
                    $this->response = array(
                        'status' => 200,
                        'data' => $data,
                        'message' => 'Vehicle Type Successfully created '
                    ); 
                }else{
                   $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not create vehicle type ",
                    ); 
                }
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

    public function get_my_trips($id = 0){
        $html ='<div class="kt-widget4">';
        $vehicle = $this->vehicles_m->get($id);
        $vehicle_trips = $this->vehicles_m->get_trips_per_vehicle($id);
            if(!empty($vehicle_trips)){
                $route_ids = [];
                $trip_ids = [];
                foreach($vehicle_trips as $trip){
                    $route_ids[] = $trip->route_id;
                    $trip_ids[] = $trip->trip_id;
                }
                
                $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                $count = 1;
                foreach($vehicle_trips as $post):
                    $from = "";
                    $destination = "";
                    $distance = "";
                    $duration = "";
                    $name = "";
                    if(array_key_exists($post->trip_id, $trips)){
                        $name = $trips[$post->trip_id]->name;

                    }
                    if(array_key_exists($post->route_id, $routes)){
                        $from = $routes[$post->route_id]->start_point;
                        $destination = $routes[$post->route_id]->end_point;
                        $distance = $routes[$post->route_id]->distance;
                        $duration = $routes[$post->route_id]->duration;
                    }
                    $html.='
                    <div class="kt-widget4__item">
                        <div class="kt-widget4__info">
                            <a class="kt-widget4__username">
                                '.$name.'
                            </a>
                            <p class="kt-widget4__text">
                                '.$from .' - '. $destination .'
                            </p>
                            <p class="kt-widget4__text">
                                ETA <strong>'.$duration .'</strong> Distance <strong>'. $distance .'</strong>
                            </p>
                        </div>
                        <a href="#" style="display:none;" class="btn btn-sm btn-label-brand btn-bold">View</a>
                    </div>';
                
                endforeach;
            }else{
                $html.='<div class="alert alert-info fade show" role="alert">
                        <div class="alert-icon"><i class="flaticon-questions-circular-button"></i></div>
                        <div class="alert-text">There are no assigned trips!</div>
                    </div>';
            }
        $html.='</div>';
        echo $html;

    }

    function get_trips_by_vehicle(){
        
        $this->form_validation->set_rules($this->validation_rules);
        //$id = $this->post->input('id');
        if($this->form_validation->run()){
            $vehicle_assign_id = $this->input->post('id');
            $vehicle_trip = $this->vehicles_m->get_assigned_trip($vehicle_assign_id);
            
            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $vehicle_trip
            );
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

    public function get_my_trips_by_vehicle(){
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $id = $this->input->post('id');
            $vehicle_trips = $this->vehicles_m->get_trips_per_vehicle($id);
            $route_ids = [];
            $trip_ids = [];
            foreach($vehicle_trips as $trip){
                $trip_ids[] = $trip->trip_id;
                $route_ids[] = $trip->route_id;
            }
            $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
            $points = $this->routes_m->get_points_by_route_ids_array_as_route_array($route_ids);
            $trips_arr = [];
            foreach($trips as $trip):
                $trips_arr[] = array(
                    'id'=> $trip->id,
                    'name' => $trip->name
                );
            
            endforeach;

            $points_arr = [];
            foreach($points as $point):
                foreach($point as $p):
                    $points_arr[] = array(
                        'id'=> $p->id,
                        'name' => $p->name
                    );
                endforeach;
            
            endforeach;

            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $trips_arr,
                'points' =>$points_arr
            );
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

    public function get_vehicle_trips($id=0){
        $html = '<div class="table-responsive">
                    <table class="table table-condensed contribution-table multiple_payment_entries" id="">
                    <thead>
                        <tr> 
                            <th width="1%">
                                #
                            </th>
                            <th width="23%">
                                Trip Name 
                            </th>
                            <th width="18%">
                                From 
                            </th>
                            <th width="18%">
                                Destination 
                            </th>
                            <th width="14%">
                                Distance 
                            </th>
                            <th width="14%"> 
                                Duration 
                            </th>
                            <th width="14%">
                               Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>'; 
                $vehicle = $this->vehicles_m->get($id);
                $vehicle_trips = $this->vehicles_m->get_trips_per_vehicle($id);
                if(!empty($vehicle_trips)){
                    $route_ids = [];
                    $trip_ids = [];
                    foreach($vehicle_trips as $trip){
                        $route_ids[] = $trip->route_id;
                        $trip_ids[] = $trip->trip_id;
                    }
                    
                    $routes = $this->routes_m->get_routes_by_ids_array($route_ids);
                    $trips = $this->trips_m->get_trips_by_ids_array($trip_ids);
                    $count = 1;
                    foreach($vehicle_trips as $post):
                        $from = "";
                        $destination = "";
                        $distance = "";
                        $duration = "";
                        $name = "";
                        $is_reverse = 0;
                        if(array_key_exists($post->trip_id, $trips)){
                            //print_r($trips); die();
                            $name = $trips[$post->trip_id]->name;
                            $is_reverse = $trips[$post->trip_id]->is_reverse;

                        }
                        if(array_key_exists($post->route_id, $routes)){
                            if($is_reverse == 0){
                                $from = $routes[$post->route_id]->start_point;
                                $destination = $routes[$post->route_id]->end_point;
                            }else{
                                $from = $routes[$post->route_id]->end_point;
                                $destination = $routes[$post->route_id]->start_point;
                            }
                           
                            $distance = $routes[$post->route_id]->distance;
                            $duration = $routes[$post->route_id]->duration;
                            
                        }
                        $html.='<tr class="'.$post->id.'_active_row">
                            <td>
                                '. $count++.' 
                            </td>
                            <td>
                                '. $name .'
                            </td>
                            <td>
                                '.  $from  .'
                            </td>
                            <td>
                                '.  $destination  .'
                            </td>
                            <td>
                                '.  $distance  .'
                            </td>
                            <td>
                                '.  $duration  .'
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-icon" id="edit_point" data-id='.$post->id.'>
                                    <i class="fa fa-edit"></i>
                                </button> | 
                                <button type="button" class="btn btn-danger btn-icon prompt_confirmation_message_link" data-id="'.$post->id.'" id="'.$post->id.'">
                                    <i class="fa fa-trash-alt"></i>
                                </button> 
                            </td>
                        </tr>';
                    
                    endforeach;
                }
        $html.='</tbody>
                </table></div></div>';

        echo $html;
    }

    public function get_vehicle_types(){
        $html = '<div class="table-responsive">
                    <table class="table table-condensed contribution-table multiple_payment_entries" id="">
                    <thead>
                        <tr> 
                            <th width="1%">
                                #
                            </th>
                            <th>
                                 Name 
                            </th>
                            <th>
                                Created On 
                            </th>
                            <th>
                               Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>'; 
                $types = $this->vehicles_m->get_all_types();
                if(!empty($types)){
                    $count = 1;
                    foreach($types as $post):
                        $html.='<tr class="'.$post->id.'_active_row">
                            <td>
                                '. $count++.' 
                            </td>
                            <td>
                                '. $post->name .'
                            </td>
                            <td>
                                '.  timestamp_to_datetime($post->created_on)  .'
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-icon" id="edit_point" data-id='.$post->id.'>
                                    <i class="fa fa-edit"></i>
                                </button> | 
                                <button type="button" class="btn btn-danger btn-icon prompt_confirmation_message_link" data-id="'.$post->id.'" id="'.$post->id.'">
                                    <i class="fa fa-trash-alt"></i>
                                </button> 
                            </td>
                        </tr>';
                    
                    endforeach;
                }
        $html.='</tbody>
                </table></div></div>';

        echo $html;
    }

    function _check_if_trip_assigned(){
        $trip_id = $this->input->post('trip_id');
        $vehicle_id = $this->input->post('vehicle_id');
        $id = $this->input->post('vehicle_assigned_id');
        if($vehicle = $this->vehicles_m->get_vehicle_assigned_trip($vehicle_id,$trip_id)){
            if($vehicle->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_trip_assigned','The route  already assigned to another vehicle.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    function _check_if_type_exist(){
        $type_id = $this->input->post('type_id');
        $name = $this->input->post('type');
        if($type = $this->vehicles_m->get_vehicle_type_by_slug(generate_slug($name))){
            if($type->id == $type_id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_check_if_type_exist','The vehicle type '. $name.' exists');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }
}