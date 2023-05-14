<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Manager extends Manager_Controller{

	
	protected $data = array();

	protected $validation_rules = array(
		array(
			'field' => 'registration',
			'label' => 'Registration Number',
			'rules' => 'required|trim|callback__is_unique_registration',
		),
        array(
            'field' => 'school_id',
            'label' => 'School Id',
            'rules' => 'trim|numeric|required',
        ),
        array(
            'field' => 'national_id',
            'label' => 'National Id',
            'rules' => 'trim|numeric',
        ),
        array(
            'field' => 'capacity',
            'label' => 'Vehicle capacity',
            'rules' => 'trim|required',
        ),
		array(
			'field' => 'type_id',
			'label' => 'Vehicle Type id',
			'rules' => 'trim|required',
		)
		
	);

	function __construct(){
        parent::__construct();
        $this->load->model('vehicles_m');
        $this->load->model('schools/schools_m');
        $this->load->model('trips/trips_m');     
    }


    function create(){
    	$post = new StdClass();
    	$this->form_validation->set_rules($this->validation_rules);
    	if($this->form_validation->run()){
    		$registration = $this->input->post('registration');
    		$type_id = $this->input->post('type_id'); 
            $types = $this->vehicles_m->get_type_option_details();
            $capacity = $this->input->post('capacity');
            $type = '';
            if(array_key_exists($type_id, $types)){
                //$capacity = $types[$type_id]->capacity;
                $type = $types[$type_id]->name;
            }

    		$data = array(
                'registration'=> $registration,
                'active'  =>1, 
                'slug'=> generate_slug($registration),
                'capacity'=>$capacity,
                'school_id'=>$this->input->post('school_id'),
                'type' =>$type,
                'type_id' => $type_id,
                'created_on'=>time(),
                'created_by'=>$this->user->id,
            );
    		$id = $this->vehicles_m->insert($data);
    		if($id){
                $vehicle = array(
                    'school_id' => $this->input->post('school_id'),
                    'vehicle_id' => $id,
                    'active' => 1,
                    'created_on'=>time(),
                    'created_by'=>$this->user->id,

                );
                if($this->vehicles_m->insert_vehicle_pairings($vehicle)){

                }else{
                   $this->session->set_flashdata('error','Vehicle could not be vehicle pairings.');
                    redirect('manager/vehicles/create');  
                }
    			$this->session->set_flashdata('success',"Vehicle registration number".$registration." created successfully");
                redirect('manager/vehicles/edit/'.$id);
    		}else{
    			$this->session->set_flashdata('error','Vehicle could not be Created.');
                redirect('manager/vehicles/create'); 
    		}
    		redirect('manager/vehicles/listing','refresh');
    	}else{
    		foreach ($this->validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
    	}
        $this->data['id'] = '';
    	$this->data['post'] = $post;
        $this->data['schools'] = $this->schools_m->get_school_options();
        $this->data['types'] = $this->vehicles_m->get_type_options();
    	$this->template->title('Create Vehicle')->build('admin/form',$this->data);
    }

    function edit($id=0){
        $id OR redirect('manager/vehicles/listing');
        $post = $this->vehicles_m->get_vehicle_school_by_id($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the vehicle does not exist');
            redirect('manager/vehicles/listing');
        }
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $types = $this->vehicles_m->get_type_option_details();
            $registration = $this->input->post('registration');
            $type_id = $this->input->post('type_id'); 
            $capacity = $this->input->post('capacity');
            $type = '';
            if(array_key_exists($type_id, $types)){
                //$capacity = $types[$type_id]->capacity;
                $type = $types[$type_id]->name;
            }
            $input = array(
                'registration'=> $registration,
                'active'  =>1, 
                'slug'=> generate_slug($registration),
                'capacity'=>$capacity,
                'type_id'=>$type_id,
                'type' => $type,
                'school_id'=>$this->input->post('school_id'),
                'modified_on'   => time(),
                'modified_by'   => $this->ion_auth->get_user()->id,
            );
            $update = $this->vehicles_m->update($post->id, $input);
            if($update){
                $vehicle = array(
                    'school_id' => $this->input->post('school_id'),
                    'active' => 1,
                    'modified_on'=>time(),
                    'modified_by'=>$this->user->id,

                );
                if(!$post->vehicle_school_id){
                    $vehicle = array(
                        'school_id' => $this->input->post('school_id'),
                        'vehicle_id' => $post->id,
                        'active' => 1,
                        'created_on'=>time(),
                        'created_by'=>$this->user->id,

                    );
                    $this->vehicles_m->insert_vehicle_pairings($vehicle);
                    $this->session->set_flashdata('success',"Vehicle updated successfully");
                }else{
                    if($this->vehicles_m->update_vehicle_pairings($post->vehicle_school_id,$vehicle)){
                        $this->session->set_flashdata('success',"Vehicle updated successfully");
                    }else{
                       $this->session->set_flashdata('error',"Vehicle could not be updated"); 
                    }
                }
               
            }else{
                $this->session->set_flashdata('error',"Vehicle could not be updated"); 
            }
            redirect('manager/vehicles/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['id'] = $id;
        $this->data['post'] = $post;
        $this->data['schools'] = $this->schools_m->get_school_options();
        $this->data['types'] = $this->vehicles_m->get_type_options();
        $this->template->title('Edit '.ucwords($post->registration))->build('admin/form',$this->data);
    }

    function view($id=0){
        $id OR redirect('manager/vehicles/listing');
        $post = $this->vehicles_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the vehicle does not exist');
            redirect('manager/vehicles/listing');
        }
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $registration = $this->input->post('registration');
            $capacity = $this->input->post('capacity');
            $type = $this->input->post('type'); 
            $input = array(
                'registration'=> $registration,
                'active'  =>1, 
                'slug'=> generate_slug($registration),
                'capacity'=>$capacity,
                'modified_on'   => time(),
                'modified_by'   => $this->ion_auth->get_user()->id,
            );
            $update = $this->vehicles_m->update($post->id, $input);
            if($update){
                $this->session->set_flashdata('success',"Vehicle updated successfully");
            }else{
                $this->session->set_flashdata('error',"Vehicle could not be updated"); 
            }
            redirect('manager/vehicles/listing','refresh');
        }else{
            foreach (array_keys($this->validation_rules) as $field){
                if (isset($_POST[$field])){
                    $post->$field = $this->form_validation->$field;
                }
            }
        }
        $this->data['id'] = $id;
        $this->data['post'] = $post;
        $this->template->set_layout('authentication/default.html')->title(' '.ucwords($post->registration))->build('admin/form',$this->data);
    }

    function trips($id=0){
        $id OR redirect('manager/vehicles/listing');
        $post = $this->vehicles_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the vehicle does not exist');
            redirect('manager/vehicles/listing');
        }
        $this->data['id'] = $id;
        $this->data['vehicle'] = $post;
        $this->data['trips'] = $this->trips_m->get_all_trips_options();
        $this->template->title(''.ucwords($post->registration).' Trips')->build('admin/trips',$this->data);
    }

    function listing(){
    	$total_rows = $this ->vehicles_m->count_all_active_vehicless();
        $pagination = create_pagination('manager/vehicles/listing/pages', $total_rows,100,5,TRUE);
        $posts = $this->vehicles_m->limit($pagination['limit'])->get_all();
        $vehicle_ids = [];
        foreach ($posts as $key => $post) {
            $vehicle_ids[] = $post->id;
        }
        $this->data['vehicles'] = $this->users_m->get_user_vehicle_id_options_by_vehicle_ids($vehicle_ids);
        $this->data['posts'] = $posts;
        $this->data['pagination'] = $pagination;
    	$this->template->title('Vehicles')->build('admin/listing',$this->data);
    }

    function types(){
        $this->template->title('Vehicles Types')->build('admin/types',$this->data);
    }

    function disable($id=0,$redirect = TRUE){
        if(!$id){
            if($redirect){
                redirect('manager/students/listing');
            }else{
                return FALSE;
            }
        }
        $post = $this->ion_auth->get_user($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the user does not exist');
            if($redirect){
                redirect('manager/users/listing');
            }else{
                return FALSE;
            }
        }

        if(!$post->active){
            $this->session->set_flashdata('error','Sorry, the user is already disabled');
            if($redirect){
                redirect('manager/users/listing');
            }else{
                return FALSE;
            }
        }else{
            if($this->ion_auth->update($post->id, array('active'=>0,'modified_on'=>time(),'modified_by'=>$this->user->id))){
                $this->session->set_flashdata('success','User successfuly disabled');
                if($redirect){
                    redirect('manager/users/listing');
                }else{
                    return TRUE;
                }
            }else{
                $this->session->set_flashdata('error','could not update user details');
                if($redirect){
                    redirect('manager/users/listing');
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

    function _is_unique_registration(){
        $slug = generate_slug($this->input->post('registration'));
        $id = $this->input->post('id');
        if($vehicle = $this->vehicles_m->get_vehicle_by_slug($slug)){
            if($vehicle->id == $id){
                return TRUE;
            }else{
                $this->form_validation->set_message('_is_unique_registration','The Vehicle with registration number '.$this->input->post('registration').' already exists.');
                return FALSE;
            }
        }else{
            return TRUE;
        }
    }
} ?>