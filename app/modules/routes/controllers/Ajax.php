<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

    protected $validation_rules = array(
        array(
            'field' => 'name',
            'label' => 'Route Name',
            'rules' => 'trim',
        ),
        array(
            'field' => 'start_point',
            'label' => 'Starting point',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'end_point',
            'label' => 'End Point',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'start_longitude',
            'label' => 'Start Longitude',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'start_latitude',
            'label' => 'Start Latitide',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'destination_longitude',
            'label' => 'Destination Longitude',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'destination_latitude',
            'label' => 'Destination Latitide',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'distance',
            'label' => 'Distance',
            'rules' => 'xss_clean|trim',
        ),
        array(
            'field' => 'duration',
            'label' => 'Duration',
            'rules' => 'xss_clean|trim',
        ),
    );

    protected $points_validation_rules = array(
        array(
            'field' => 'drop_point',
            'label' => 'Route Name',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'id',
            'label' => 'Route point id',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'drop_longitude',
            'label' => 'Longitude',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'drop_latitude',
            'label' => 'Latitide',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'drop_distance',
            'label' => 'Distance',
            'rules' => 'xss_clean|trim',
        ),
        array(
            'field' => 'drop_duration',
            'label' => 'Duration',
            'rules' => 'xss_clean|trim',
        ),
    );

    protected $id_rules = array(
        array(
            'field' => 'id',
            'label' => 'Route point id',
            'rules' => 'required|trim',
        )
    );

    function __construct(){
        parent::__construct();
        $this->load->model('routes_m');   
    }

    public function create(){
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $name = $this->input->post('name');
            $start_point = $this->input->post('start_point');
            $end_point = $this->input->post('end_point');
            $start_longitude = $this->input->post('start_longitude');
            $start_latitude = $this->input->post('start_latitude');
            $destination_longitude = $this->input->post('destination_longitude');
            $destination_latitude = $this->input->post('destination_latitude');
            $distance = $this->input->post('distance');
            $duration = $this->input->post('duration');
            $confirmation_code = rand(1000,9999);
            $input = array(
                'name'  => $start_point." - ".$end_point,
                'start_point'  => $start_point,
                'end_point'  => $end_point,
                'start_longitude'  => $start_longitude,
                'start_latitude'  =>  $start_latitude,
                'destination_longitude ' => $destination_longitude,
                'destination_latitude'  => $destination_latitude,
                'distance'  =>  $distance,
                'duration'  => $duration,
            );
            $id = $this->input->post('id'); 
            if($exist = $this->routes_m->get($id)){
                $input = $input + array(                
                    'active' =>1,
                    'modified_on'=>time(),
                    'modified_by'=> $this->user->id,
                );
                if($update =  $this->routes_m->update($id,$input)){ 
                    $this->response = array(
                        'result_code' => 200,
                        'message' => "Route name ". $name ." successfuly updated",
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not create a route ",
                    );                
                }
            }else{
                $input = $input + array(                
                    'active' =>1,
                    'created_on'=>      time(),
                    'created_by'=>      $this->user->id,
                );
                $id = $this->routes_m->insert($input);
                if($id){ 
                    $this->response = array(
                        'result_code' => 200,
                        'message' => "Route name ". $name ." successfuly created",
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not create a route ",
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

    public function create_drop(){
        $this->form_validation->set_rules($this->points_validation_rules);
        if($this->form_validation->run()){
            $name = $this->input->post('drop_point');
            $route_id = $this->input->post('id');
            $longitude = $this->input->post('drop_longitude');
            $latitude = $this->input->post('drop_latitude');
            $distance = $this->input->post('distance');
            $duration = $this->input->post('duration');
            $input = array(
                'name'  => $name,
                'slug' => generate_slug($name),
                'route_id'  => $route_id,
                'longitude'  => $longitude,
                'latitude'  =>  $latitude,
                'distance'  =>  $distance,
                'duration'  => $duration,
                'active' => 1,
            ); 
            $route_id = $this->input->post('route_id');
            if($route_id){
                $exist = $this->routes_m->get_points($route_id);
                $input = $input + array(                
                    'active' =>1,
                    'modified_on'=>time(),
                    'modified_by'=> $this->user->id,
                );
                if($update =  $this->routes_m->update_points($exist->id,$input)){ 
                    $this->response = array(
                        'result_code' => 200,
                        'message' => "Route name ". $name ." successfuly updated",
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not create a route ",
                    );                
                }
            }else{
                if($exist = $this->routes_m->get_drop_by_slug(generate_slug($name))){
                    $input = $input + array(                
                        'active' =>1,
                        'modified_on'=>time(),
                        'modified_by'=> $this->user->id,
                    );
                    if($update =  $this->routes_m->update_points($exist->id,$input)){ 
                        $this->response = array(
                            'result_code' => 200,
                            'message' => "Route name ". $name ." successfuly updated",
                        );
                    }else{
                        $this->response = array(
                            'result_code' => 400,
                            'message' => "Could not create a route ",
                        );                
                    }
                }else{
                    $input = $input + array(                
                        'active' =>1,
                        'created_on'=>      time(),
                        'created_by'=>      $this->user->id,
                    );
                    $id = $this->routes_m->insert_points($input);
                    if($id){ 
                        $this->response = array(
                            'result_code' => 200,
                            'message' => "Route name ". $name ." successfuly created",
                        );
                    }else{
                        $this->response = array(
                            'result_code' => 400,
                            'message' => "Could not create a route ",
                        );                
                    }
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

    public function get_point(){
        $this->form_validation->set_rules($this->id_rules);
        if($this->form_validation->run()){
            $id = $this->input->post('id');
            $post = $this->routes_m->get_points($id);
            if($post){
                $this->response = array(
                    'result_code' => 200,
                    'message' => 'Success',
                    'data' => $post
                );
            }else{
                $this->response = array(
                    'result_code' => 400,
                    'message' => 'Route not found',
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

    public function get_points($id=0){
        $html = '<div class="table-responsive">
                    <table class="table table-condensed contribution-table multiple_payment_entries" id="">
                    <thead>
                        <tr> 
                            <th width="1%">
                                #
                            </th>
                            <th width="23%">
                                Drop Point <span class="required">*</span>
                            </th>
                            <th width="18%">
                                Longitude <span class="required">*</span>
                            </th>
                            <th width="18%">
                                Latitide <span class="required">*</span>
                            </th>
                            <th width="18%">
                                Distance <span class="required">*</span>
                            </th>
                            <th width="18%">
                                Duration <span class="required">*</span>
                            </th>
                            <th width="4%">
                               &nbsp;
                            </th>
                        </tr>
                    </thead>
                    <tbody>';
                $route = $this->routes_m->get($id);
                $points = $this->routes_m->get_points_by_route_id($id);
                if(!empty($points)){
                    $count = 1;
                    foreach($points as $post):
                        $html.='<tr>
                            <td>
                                '. $count++.' 
                            </td>
                            <td>
                                '. $post->name .'
                            </td>
                            <td>
                                '.  $post->longitude  .'
                            </td>
                            <td>
                                '.  $post->latitude  .'
                            </td>
                            <td>
                                '.  $post->distance  .'
                            </td>
                            <td>
                                '.  $post->duration  .'
                            </td>
                            <td>
                                <button class="btn m-btn--pill btn-sm m-btn--air btn-primary btn-sm " id="edit_point" data-id='.$post->id.'>
                                    <i class="fa fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>';
                    
                    endforeach;
                }
        $html.='</tbody>
                </table></div></div>';

        echo $html;
    }
}