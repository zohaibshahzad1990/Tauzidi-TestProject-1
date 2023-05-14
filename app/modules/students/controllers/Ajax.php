<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

    protected $validation_rules = array(
        array(
            'field' => 'name',
            'label' => 'Full Name',
            'rules' => 'required|trim',
        ),
        array(
            'field' => 'school_id',
            'label' => 'School details',
            'rules' => 'trim|numeric|required',
        ),
        array(
            'field' => 'parent_id',
            'label' => 'Parent details',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'user_parent_id',
            'label' => 'Parent details',
            'rules' => 'trim|required',
        ),
        
        array(
            'field' => 'vehicle_id',
            'label' => 'Vehicle details',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'trip_id[]',
            'label' => 'Trip details',
            'rules' => 'trim|required',
        ),
         array(
            'field' => 'registration_no',
            'label' => 'Student Registration Number',
            'rules' => 'trim|required|callback__check_if_student_number_is_unique',
        ),
    );

    protected $validation_student_rules = array(
        array(
            'field' => 'id',
            'label' => 'Student Id',
            'rules' => 'trim|required',
        )
    );

    function __construct(){
        parent::__construct();
        $this->load->model('students_m');
        $this->load->model('parents/parents_m');
        $this->load->model('schools/schools_m'); 
        $this->load->model('vehicles/vehicles_m'); 
        $this->load->library('schools_manager');   
    }

    function create(){
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $name = $this->input->post('name');
            $parent_id = $this->input->post('parent_id');
            $user_parent_id = $this->input->post('user_parent_id');
            $school_id = $this->input->post('school_id');
            $student_id = $this->input->post('student_id');
            $vehicle_id = $this->input->post('vehicle_id');
            $trip_ids = $this->input->post('trip_id');
            $registration_no = $this->input->post('registration_no');
            $full_name = $name;
            $full_name_array = explode(' ',$full_name);
            if(count($full_name_array) == 2){
              $first_name = $full_name_array[0];
              $last_name = $full_name_array[1];
              $middle_name = "";
            }else{
              $first_name = $full_name_array[0];
              $middle_name = array_key_exists(1, $full_name_array) ? $full_name_array[1]: "";
              $last_name_array = array();
              for($i = 2; $i < count($full_name_array); $i++){
                $last_name_array[] = $full_name_array[$i].' ';
              }
              $last_name = implode(' ',$last_name_array);
            }
            $phone = "";
            $password = "";
            $email = "";
            $student_exist = $this->students_m->get($student_id);
            if(!$student_exist){
                $additional_data = array(
                    'username'   => $first_name,
                    'active'     => 1, 
                    'ussd_pin'   => rand(1000,9999),
                    'first_name' => $first_name, 
                    'middle_name'=> $middle_name, 
                    'last_name'  => $last_name,
                    'point_id' => $this->input->post('point_id'),
                    'created_on' => time(),
                    'created_by' => $this->user->id,
                );
                $group = $this->ion_auth->get_group_by_name('student');
                $groups = array($group->id);
                $id = $this->ion_auth->register($phone,$password,$email, $additional_data,$groups);
                if($id){
                    $student_input = array( 
                        'parent_id' =>$parent_id,
                        'user_parent_id' => $user_parent_id,
                        'user_id'=> $id,
                        'registration_no'=>$registration_no,
                        'school_id'=> $school_id,
                        'vehicle_id'=>$vehicle_id, 
                        'point_id' => $this->input->post('point_id'),              
                        'active' =>1,
                        'created_on'=>time(),
                        'created_by'=> $this->user->id,
                    );
                    if($update =  $this->students_m->insert($student_input)){ 
                        if(count($trip_ids) > 0 ){
                            foreach ($trip_ids as $key => $trip_id) {
                                $student_trips = array(
                                    'trip_id' => $trip_id,
                                    'student_id' =>$update,
                                    'school_id' =>$school_id,
                                    'vehicle_id' =>$vehicle_id,
                                    'route_id' =>'',
                                    'parent_id' =>$parent_id,
                                    'active' =>1,
                                    'created_on' => time(),
                                    'created_by' => $this->user->id,
                                );
                                $this->students_m->insert_trips($student_trips);
                            }
                        }
                        $this->response = array(
                            'result_code' => 200,
                            'message' => "Student name ". $first_name ." successfuly created",
                        );
                    }else{
                        $this->response = array(
                            'result_code' => 400,
                            'message' => "Could not create a student ",
                        );                
                    }
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not create a customer ",
                    );
                }
            }else{

                $input = array(
                    'username'   => $first_name,
                    'active'     => 1, 
                    'ussd_pin'   => rand(1000,9999),
                    'first_name' => $first_name, 
                    'middle_name'=> $middle_name, 
                    'last_name'  => $last_name,
                    'modified_on'   => time(),
                    'modified_by'   => $this->ion_auth->get_user()->id,
                );
                $update = $this->ion_auth->update($student_exist->user_id, $input);
                if($update){
                    $student_update = array(
                        'parent_id' =>$parent_id,
                        'user_parent_id' => $parent_id,
                        'school_id'=> $school_id,
                        'vehicle_id'=>'',               
                        'active' =>1,
                        'modified_on'=>time(),
                        'modified_by'=> $this->user->id,
                    );
                    $this->students_m->update($student_exist->id,$student_update);
                    $trips = $this->students_m->get_trips_by_student_id($student_exist->id);
                    if(!empty($trips)){
                        print_r($trips); die();
                    }
                    $this->response = array(
                        'result_code' => 200,
                        'message' => "Student name ". $name ." successfuly updated",
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => "Could not update a student ",
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

    function get_my_student(){
        
        $this->form_validation->set_rules($this->validation_student_rules);
        if($this->form_validation->run()){
            $student_id = $this->input->post('id');
            $student = $this->students_m->get_user_by_student_id($student_id);
            $trips = $this->students_m->get_trips_by_student_id($student_id);
            $trip_ids = [];
            foreach ($trips as $key => $trip) {
                $trip_ids[] = $trip->trip_id;
            }
            $this->response = array(
                'result_code' => 200,
                'message' => 'Successful',
                'data' => $student,
                'trips' => $trip_ids
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

    function delete_student(){
        $this->form_validation->set_rules($this->validation_student_rules);
        if($this->form_validation->run()){
            $student_id = $this->input->post('id');
            $student = $this->students_m->get_user_by_student_id($student_id);
            if($student){
                $input = array(
                    'active' => 0,
                    'modified_on'=>time(),
                    'modified_by' => $this->user->id
                );
                if($update = $this->students_m->update($student_id,$input)){
                    $this->users_m->update($student->id, $input);
                    $this->response = array(
                        'result_code' => 200,
                        'message' => 'Successful',
                        'data' => $student
                    );
                }else{
                    $this->response = array(
                        'result_code' => 400,
                        'message' => 'Coul not delete student details',
                    );
                }
            }else{
                $this->response = array(
                    'result_code' => 400,
                    'message' => 'Coul not get student details',
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

    public function get_my_students($id=0){
        $html = '<div class="table-responsive">
                    <table class="table table-condensed contribution-table multiple_payment_entries" id="">
                    <thead>
                        <tr> 
                            <th width="1%">
                                #
                            </th>
                            <th width="20%">
                                Name 
                            </th>
                            <th width="20%">
                                Registration Number 
                            </th>
                            <th width="18%">
                                School 
                            </th>
                            <th width="14%">
                                Vehicle 
                            </th>
                            <th width="14%">
                               Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>';
                $parent = $this->users_m->get($id);
                
                $students = $this->students_m->get_students_per_parent($id);
                if(!empty($students)){
                    $user_ids = [];
                    $school_ids = [];
                    $vehicle_ids = [];
                    foreach($students as $student){
                        $user_ids[] = $student->user_id;
                        $school_ids[] = $student->school_id;
                        $vehicle_ids[] = $student->vehicle_id;
                    }
                    
                    $schools = $this->schools_m->get_school_options_by_ids($school_ids);
                    $users = $this->users_m->get_user_array_options($user_ids);
                    $vehicles = $this->vehicles_m->get_vehicle_by_ids_options($vehicle_ids);
                    $count = 1;
                    foreach($students as $student):
                        $school_name = "";
                        $name = "";
                        $vehicle = "";
                        $school_registration = "";
                        if(array_key_exists($student->user_id, $users)){
                            $name = $users[$student->user_id]->first_name .' '.$users[$student->user_id]->last_name; 
                        }
                        if(array_key_exists($student->school_id, $schools)){

                            $school_name = $schools[$student->school_id];
                        }
                        $registration = "";
                        if(array_key_exists($student->vehicle_id, $vehicles)){

                            $registration = $vehicles[$student->vehicle_id];
                        }
                        $html.='<tr class="'.$student->id.'_active_row">
                            <td> 
                                '. $count++.' 
                            </td>
                            <td>
                                '. $name .'
                            </td>
                            <td>
                                '. $student->registration_no .'
                            </td>
                            <td>
                                '.  $school_name  .'
                            </td>
                            <td>
                                '. $registration .'
                            </td>
                            <td>
                                <a type="button" class="btn btn-primary btn-icon" id="" href='.site_url("admin/students/edit/".$student->id).' data-id='.$student->id.'>
                                    <i class="fa fa-edit"></i>
                                </a> | 
                                <button type="button" class="btn btn-danger btn-icon prompt_confirmation_message_link" data-id="'.$student->id.'" id="'.$student->id.'">
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

    function _check_if_student_number_is_unique(){
        $registration_no = $this->input->post('registration_no');
        $student_id = $this->input->post('student_id');
        if($student = $this->students_m->get_student_by_registration_number($registration_no)){
            $this->form_validation->set_message('_check_if_student_number_is_unique','The student number is already registered to another account in the system');
            return FALSE;
        }else{
          return TRUE;
        }
    }

}