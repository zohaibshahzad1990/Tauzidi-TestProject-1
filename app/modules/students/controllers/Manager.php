<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Manager extends Manager_Controller{

	
	protected $data = array();

	function __construct(){
        parent::__construct();
        $this->load->model('users_m');
        $this->load->model('user_groups/user_groups_m');
        $this->load->model('schools/schools_m'); 
        $this->load->model('students/students_m');
        $this->load->model('parents/parents_m');  
        $this->load->model('trips/trips_m'); 
        $this->load->model('routes/routes_m'); 
        $this->load->model('vehicles/vehicles_m'); 
        $this->load->library('schools_manager');   
    }


    function student_options(){
        $vehicle_id = $this->input->post_get('vehicle_id');
        $total_rows = $this->students_m->count_vehicles_per_vehicle($vehicle_id);;
        $pagination = create_pagination('manager/students/student_options/pages', $total_rows,100,5,TRUE);
        $vehicle_ids = [];
        $students = $this->students_m->limit($pagination['limit'])->get_students_per_vehicle($vehicle_id);
        $student_user_ids = [];
        $vehicle = $this->vehicles_m->get($vehicle_id);
        $school_ids = [];
        $user_parent_ids = [];

        foreach ($students as $key => $student) {
            $student_user_ids[] = $student->user_id;
            $school_ids[] = $student->school_id;
            if($student->user_parent_id){
                $user_parent_ids[] = $student->user_parent_id;
            }
            $vehicle_ids[] = $student->vehicle_id;
        }
        $user_students = $this->students_m->get_user_options_by_student_ids($student_user_ids);
        $this->data['schools'] = $this->schools_m->get_school_options_by_ids($school_ids);
        $vehicles = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
        $this->data['vehicles'] = $vehicles;
        $this->data['posts'] = $students;
        $this->data['user_students'] = $user_students;
        $this->data['pagination'] = $pagination;
        $this->data['parents'] = $this->parents_m->get_parent_options_by_user_ids($user_parent_ids);
        $this->template->title('Students')->build('admin/student_options',$this->data);
    }
} ?>