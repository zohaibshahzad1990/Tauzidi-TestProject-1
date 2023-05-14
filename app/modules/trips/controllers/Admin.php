<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Admin_Controller{

    protected $data = array();

    protected $validation_rules = array(
		array(
			'field' => 'name',
			'label' => 'Route Name',
			'rules' => 'required|trim|callback__slug_is_unique',
		),
        array(
            'field' => 'school_id',
            'label' => 'School',
            'rules' => 'trim|',
        ),
        array(
            'field' => 'route_id',
            'label' => 'Route',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'vehicle_id',
            'label' => 'Vehicle',
            'rules' => 'trim|',
        ),
        array(
            'field' => 'trip_time',
            'label' => 'Time',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'trip_type_id',
            'label' => 'Trip Type',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'return_name',
            'label' => 'Return Name',
            'rules' => 'trim|callback__trip_return_slug_is_unique',
        ),
        array(
            'field' => 'return_trip_time',
            'label' => 'Return trip time',
            'rules' => 'trim|callback__check_if_type_is_checked',
        ),
        
		
	);

     public $trip_type = array(
        '1' => "One Way",
        '2' => 'With Return Trip'
    );

    public function __construct(){
        parent::__construct();
        $this->load->model('trips_m');
        $this->load->model('routes/routes_m');
        $this->load->model('schools/schools_m');
        $this->load->model('vehicles/vehicles_m');

    }

    public function create(){
    	$post = new StdClass();
    	$this->form_validation->set_rules($this->validation_rules);
    	if($this->form_validation->run()){
    		$name = $this->input->post('name');
            $route_id = $this->input->post('route_id');
            $school_id = $this->input->post('school_id');
            $vehicle_id = $this->input->post('vehicle_id');
            $trip_time = $this->input->post('trip_time');
            $trip_type_id = $this->input->post('trip_type_id');
            $return_name = $this->input->post('return_name');
            $return_trip_time = $this->input->post('return_trip_time');
            $input = array(
                'name'  => $name,
                'slug' => generate_slug($name),
                'route_id'  => $route_id,
                'vehicle_id' => $vehicle_id,
                'school_id'  => $school_id,
                'trip_time' =>$trip_time,
                'parent_id'=>0,
                'is_reverse'=>0,
                'trip_type_id'=>$trip_type_id,
                'active' =>1,
                'created_on' =>time(),
                'created_by'=>$this->user->id,
            );

            $new = $this->trips_m->insert($input);
            if($new){
                if($trip_type_id == 2){
                    $input_return = array(
                        'name'  => $return_name,
                        'slug' => generate_slug($return_name),
                        'route_id'  => $route_id,
                        'vehicle_id' => $vehicle_id,
                        'trip_type_id'=>$trip_type_id,
                        'school_id'  => $school_id,
                        'is_reverse'=>1,
                        'trip_time' =>$return_trip_time,
                        'parent_id'=>$new,
                        'active' =>1,
                        'created_on' =>time(),
                        'created_by'=>$this->user->id,
                    );
                    $return_trip_i = $this->trips_m->insert($input_return);
                }
                $this->session->set_flashdata('success',"Trip created successfully");
            }else{
                $this->session->set_flashdata('error',"Could not create trip");
                redirect('admin/trips/create');
            }
            redirect('admin/trips/listing','refresh');
    	}else{
    		foreach ($this->validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
    	$this->data['post'] = $post;
    	$this->data['id']='';
        $this->data['return_trip_id']= "";
        $this->data['trip_type']=$this->trip_type;
        $this->data['return_name']='';
    	$this->data['schools'] = $this->schools_m->get_school_options();
    	$this->data['routes'] = $this->routes_m->get_routes_option();
        $this->template->title('Create Trip')->build('admin/form', $this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/trips/listing');
        $post = $this->trips_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the trip does not exist');
            redirect('admin/trips/listing');
        }
        $child_trip = $this->trips_m->get_child_trip($post->id);
        $post->return_name = $child_trip ? $child_trip->name : "";
        $post->return_trip_id = $child_trip ? $child_trip->id : "";
        $post->return_trip_time = $child_trip ? $child_trip->trip_time : "";
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $name = $this->input->post('name');
            $route_id = $this->input->post('route_id');
            $school_id = $this->input->post('school_id');
            $vehicle_id = $this->input->post('vehicle_id');
            $trip_time = $this->input->post('trip_time');

            $trip_type_id = $this->input->post('trip_type_id');
            $return_name = $this->input->post('return_name');
            $return_trip_time = $this->input->post('return_trip_time');
            $input = array(
                'name'  => $name,
                'slug' => generate_slug($name),
                'route_id'  => $route_id,
                'school_id'  => $school_id,
                'vehicle_id' => $vehicle_id,
                'trip_time'=>$trip_time,
                'parent_id'=>0,
                'is_reverse'=>0,
                'trip_type_id'=>$trip_type_id,
                'active' =>1,
                'modified_on' =>time(),
                'modified_by'=>$this->user->id,
            );
            $update = $this->trips_m->update($post->id, $input);
            if($update){
                if($trip_type_id == 2){

                    if(!$post->return_trip_id){
                        $input_return = array(
                            'name'  => $return_name,
                            'slug' => generate_slug($return_name),
                            'route_id'  => $route_id,
                            'vehicle_id' => $vehicle_id,
                            'trip_type_id'=>$trip_type_id,
                            'school_id'  => $school_id,
                            'is_reverse'=>1,
                            'trip_time' =>$return_trip_time,
                            'parent_id'=>$post->id,
                            'active' =>1,
                            'created_on' =>time(),
                            'created_by'=>$this->user->id,
                        );
                        $return_trip_i = $this->trips_m->insert($input_return);
                    }else{
                        $input_return = array(
                            'name'  => $return_name,
                            'slug' => generate_slug($return_name),
                            'route_id'  => $route_id,
                            'vehicle_id' => $vehicle_id,
                            'trip_type_id'=>$trip_type_id,
                            'school_id'  => $school_id,
                            'parent_id'=>$post->id,
                            'is_reverse'=>1,
                            'trip_time' =>$return_trip_time,
                            'active' =>1,
                            'modified_on' =>time(),
                            'modified_by'=>$this->user->id,
                        );
                        $this->trips_m->update($post->return_trip_id, $input_return);
                    }
                }
                $this->session->set_flashdata('success',"Trip updated successfully");
            }else{
                $this->session->set_flashdata('error',"Could not update trip");
                redirect('admin/trips/edit/'.$id); 
            }
            redirect('admin/trips/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;

                }
            }
        }
        $this->data['id']=$post->id;
        $this->data['post'] = $post;
        $this->data['schools'] = $this->schools_m->get_school_options();
    	$this->data['routes'] = $this->routes_m->get_routes_option();
    	$this->data['vehicles'] = array();
        $this->data['trip_type']=$this->trip_type;
        $this->data['return_trip_id']= $child_trip ? $child_trip->id : "";
        $this->data['return_trip_time']= $child_trip ? $child_trip->trip_time : "";
        //print_r($child_trip);
        //print_r($this->data); die();
        $this->template->title('Edit '.ucwords($post->name))->build('admin/form',$this->data);
    }

    function active_journey($id =0){
    	$id OR redirect('admin/trips/listing');
        $post = $this->trips_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the trip does not exist');
            redirect('admin/trips/listing');
        }
        $journey = $this->trips_m->get_journey_by_trip_vehicle_route($post->id,$post->vehicle_id,$post->route_id);
        $this->data['journey']  = $journey;
        $this->data['posts'] = array();
        $this->data['trip'] = $post;
        $this->data['route'] = $this->routes_m->get($post->route_id);
        $this->data['vehicle'] = $this->vehicles_m->get($post->vehicle_id);
        $this->data['driver'] = $this->users_m->get_user_by_driver_id($journey->driver_id);
        //print_r($this->data); die();
        $this->template->title('Active Trips '.ucwords($post->name))->build('admin/active_trips',$this->data);
    }

    function history($id =0){
    	$id OR redirect('admin/trips/listing');
        $post = $this->trips_m->get_journey($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the trip does not exist');
            redirect('admin/trips/listing');
        }
        $route = $this->routes_m->get($post->route_id);
        $trip = $this->trips_m->get($post->trip_id);
        //print_r($route);
        //print_r($route); die();
        //$edges = $this->trips_m->get_cordinates_edges($id);
        $cord = $this->trips_m->get_random_coridnates($id);
        //print_r($cord); die();
        $diffence = 0;
        $cordinate_arr = [];
        $cordinate_end = [];
        $cordinate_start = [];
        foreach ($cord as $key => $journey) {
            $journey = (object)$journey;
            $cordinate_arr [] = (object)array(
                "latitude" => $journey->latitude,
                "longitude" => $journey->longitude,
                "timestamp" => $trip->name,
                "description" =>"",
                "address2" => "",
                "postalCode" => ""
            );
        }
        $final_merge = $cordinate_arr;
        $this->data['journey_cordinates']  = $final_merge;
        $this->data['route'] = $route;
        $this->data['trip'] = $trip;
        //print_r($this->data); die();
        $this->template->title('Trips '.ucwords($trip->name))->build('admin/trips',$this->data);
    }

    function listing(){
    	$total_rows = $this ->trips_m->count_active_trips();
        $pagination = create_pagination('admin/trips/listing/pages', $total_rows,100,5,TRUE);
        $this->data['schools'] = $this->schools_m->get_school_options();
    	$this->data['routes'] = $this->routes_m->get_routes__full_option();
        $this->data['posts'] = $this->trips_m->limit($pagination['limit'])->get_all();
        $this->data['vehicles'] = $this->vehicles_m->get_veheicle_options();
        $this->data['pagination'] = $pagination;
    	$this->template->title('Trips Listing')->build('admin/listing',$this->data);
    }

    function _slug_is_unique(){
        $slug = generate_slug($this->input->post('name')); 
        $user_id = $this->user->id;
        $name = $this->input->post('name');
        $id = $this->input->post('id');
        if($trip = $this->trips_m->get_trip_by_slug($slug)){
            if($trip->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_slug_is_unique','The trip '.$name.' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }

    function _trip_return_slug_is_unique(){
        $slug = generate_slug($this->input->post('return_name'));
        $user_id = $this->user->id;
        $name = $this->input->post('return_name');
        $id = $this->input->post('return_trip_id');
       
        if($slug ==  generate_slug($this->input->post('name'))){
            $this->form_validation->set_message('_trip_return_slug_is_unique','Return trip '.$name.' cannot be same as trip name.');
            return FALSE;
        }else{
            if($trip = $this->trips_m->get_trip_by_slug($slug)){
                if($trip->id == $id){
                    return TRUE;
                }else{
                    $this->form_validation->set_message('_trip_return_slug_is_unique','Return trip '.$name.' already exists.');
                    return FALSE;
                }
            }else{
                return TRUE;
            }
        }
    }

    function _check_if_type_is_checked(){
        $trip_type_id = $this->input->post('trip_type_id');
        $user_id = $this->user->id;
        $return_name = $this->input->post('return_name');
        $return_trip_time = $this->input->post('return_trip_time');

        if($trip_type_id == 2){
            if($return_name){
                if($return_trip_time){
                    return TRUE;
                }else{
                    $this->form_validation->set_message('_check_if_type_is_checked','The trip  return time is required.');
                    return FALSE;
                }
            }else{
                $this->form_validation->set_message('_check_if_type_is_checked','The trip  return name is required.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }



} ?>