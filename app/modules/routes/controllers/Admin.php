<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{
	
	protected $data = array();

	protected $validation_rules = array(
		array(
			'field' => 'name',
			'label' => 'Route Name',
			'rules' => 'required|trim',
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
            'field' => 'drop_name',
            'label' => 'Route Name',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'route_id',
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

	function __construct(){
        parent::__construct();
        $this->load->model('routes_m');   
    }

    function create(){
    	$post = new StdClass();
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
                'name'  => $name,
                'start_point'  => $start_point,
                'end_point'  => $end_point,
                'start_longitude'  => $start_longitude,
                'start_latitude'  =>  $start_latitude,
                'destination_longitude ' => $destination_longitude,
                'destination_latitude'  => $destination_latitude,
                'distance'  =>  $distance,
                'duration'  => $duration,
                'active' =>1,
                'created_on'        =>      time(),
                'created_by'        =>      $this->user->id,
            );
    		$id = $this->routes_m->insert($input);
    		if($id){                
    			$this->session->set_flashdata('success',"Route name ". $name ." successfuly created");
                redirect('admin/routes/edit/'.$id);
    		}else{
    			$this->session->set_flashdata('error',"Could not create a route ".$this->session->warning); 
    		}
    		redirect('admin/routes/listing','refresh');
    	}else{
    		foreach ($this->validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
        $this->data['id']='';
    	$this->data['post'] = $post;
    	$this->template->title('Create Route')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('admin/routes/listing');
        $post = $this->routes_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the route does not exist');
            redirect('admin/routes/listing');
        }
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
            $input = array(
                'name'  => $name,
                'start_point'  => $start_point,
                'end_point'  => $end_point,
                'start_longitude'  => $start_longitude,
                'start_latitude'  =>  $start_latitude,
                'destination_longitude ' => $destination_longitude,
                'destination_latitude'  => $destination_latitude,
                'distance'  =>  $distance,
                'duration'  => $duration,
                'active' =>1,
                'modified_on' =>time(),
                'modified_by'=>$this->user->id,
            );
            $update = $this->routes_m->update($post->id, $input);
            if($update){
                $this->session->set_flashdata('success',"Route updated successfully");
            }else{
                $this->session->set_flashdata('error',"Could not update route");
                redirect('admin/routes/edit/'.$id); 
            }
            redirect('admin/routes/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['id']=$post->id;
        $this->data['post'] = $post;
        $this->template->title('Edit '.ucwords($post->name))->build('admin/form',$this->data);
    }

    function points($id=0){
        $id OR redirect('admin/routes/listing');
        $route = $this->routes_m->get($id);
        $post = new StdClass();
        if(!$route){
            $this->session->set_flashdata('error','Sorry, the route does not exist');
            redirect('admin/routes/listing');
        }
        $points = $this->routes_m->get_points_by_route_id($id);
        $this->form_validation->set_rules($this->points_validation_rules);
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
            $input = array(
                'name'  => $name,
                'start_point'  => $start_point,
                'end_point'  => $end_point,
                'start_longitude'  => $start_longitude,
                'start_latitude'  =>  $start_latitude,
                'destination_longitude ' => $destination_longitude,
                'destination_latitude'  => $destination_latitude,
                'distance'  =>  $distance,
                'duration'  => $duration,
                'active' =>1,
                'modified_on' =>time(),
                'modified_by'=>$this->user->id,
            );
            $update = $this->routes_m->update($post->id, $input);
            if($update){
                $this->session->set_flashdata('success',"Route updated successfully");
            }else{
                $this->session->set_flashdata('error',"Could not update route");
                redirect('admin/routes/edit/'.$id); 
            }
            redirect('admin/routes/listing','refresh');
        }else{
            foreach ($this->points_validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
        }
        $this->data['id']=$route->id;
        $this->data['points'] = $points;
        $this->data['route'] = $route;
        $this->template->title('Plot Points '.ucwords($route->name))->build('admin/points',$this->data);
    }

    function listing(){
    	$total_rows = $this ->routes_m->count_routes();
        $pagination = create_pagination('admin/routes/listing/pages', $total_rows,100,5,TRUE);
        $this->data['posts'] = $this->routes_m->limit($pagination['limit'])->get_all();
        $this->data['pagination'] = $pagination;
    	$this->template->title('Routes ')->build('admin/listing',$this->data);
    }

    function disable($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/users/listing');
            }else{
                return FALSE;
            }
        }

        if(!$post->active){
            $this->session->set_flashdata('error','Sorry, the user is already disabled');
            if($redirect){
                redirect('admin/users/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>0,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User successfuly disabled');
                if($redirect){
                    redirect('admin/users/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/users/listing');
                }else{
                    return TRUE;
                }
            }
            
        }
    }

    function activate($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }

        if($post->active){
            $this->session->set_flashdata('error','Sorry, the user account is already active');
            if($redirect){
                redirect('admin/students/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>1,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User account successfuly activated');
                if($redirect){
                    redirect('admin/students/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('admin/students/listing');
                }else{
                    return TRUE;
                }
            }
            
        }
    }

    function action(){
        $btnAction = $this->input->post('btnAction');
        $action_to = $this->input->post('action_to');
        if($action_to){
            foreach ($action_to as $id) {
                if($btnAction=='bulk_disable'){
                    $this->disable($id,FALSE);
                }else if ($btnAction=='bulk_activate') {
                    $this->activate($id,FALSE);
                }
            }
        }
        redirect('admin/students/listing');
    }

    function ajax_search_options(){
        $this->users_m->get_search_options();
    }


}