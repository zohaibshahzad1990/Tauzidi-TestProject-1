<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ajax extends Ajax_Controller{

    function __construct(){
        parent::__construct();
        $this->load->model('users/users_m');
        $this->load->model('user_groups/user_groups_m');
        $this->load->model('parents/parents_m'); 
        $this->load->model('guardians/guardians_m');   
    }


    function create(){
        $data = array();
        $posts = $_POST;
        $errors = array();
        $response = array();
        $error_messages = array();
        $successes = array();
        $entries_are_valid = TRUE;
        if($posts){
            if(empty($posts)){ 
                $response = array(
                    'status' => 0,
                    'message' => 'You have not submitted any guardians to process',
                );
            }else{
                if(isset($posts['full_names'])){
                    $count = count($posts['full_names']);
                    for($i=0;$i<=$count;$i++):
                        if(isset($posts['full_names'][$i])&&isset($posts['phones'][$i])&&isset($posts['id_numbers'][$i])
                    ):

                            //fixture dates
                            if($posts['full_names'][$i]==''){
                                $successes['full_names'][$i] = 0;
                                $errors['full_names'][$i] = 1;
                                $error_messages['full_names'][$i] = 'Full name is required';
                                $entries_are_valid = FALSE;
                            }else{
                                $successes['full_names'][$i] = 1;
                                $errors['full_names'][$i] = 0;
                            }

                            //leagues
                            if($posts['phones'][$i]==''){
                                $successes['phones'][$i] = 0;
                                $errors['phones'][$i] = 1;
                                $error_messages['phones'][$i] = 'Phone number ';
                                $entries_are_valid = FALSE;
                            }else{
                                $successes['phones'][$i] = 1;
                                $errors['phones'][$i] = 0;
                            }

                            if($posts['id_numbers'][$i]==''){
                                $successes[$i]['id_numbers'] = 0;
                                $errors[$i]['id_numbers'] = 1;
                                $error_messages[$i]['id_numbers'] = 'Id number required';
                                $entries_are_valid = FALSE;
                            }else{
                                $successes[$i]['id_numbers'] = 1;
                                $errors[$i]['id_numbers'] = 0;
                            }

                        endif;
                    endfor;
                }else{
                    $entries_are_valid = FALSE;
                }
                if($entries_are_valid){
                    $phones = $posts['phones']; 

                    $valid_phones_arr = [];
                    foreach ($posts['phones'] as $key => $phone) {
                        //$phone = valid_phone($phone);
                        //print_r($phone); die;
                        $valid_phones_arr[] = valid_phone($phone);
                    }
                    //$user_options_phone = $this->users_m->get_user_by_phone_options($valid_phones_arr);
                    //print_r($user_options_phone); die();
                    $input_array = array();
                    $count_create = 0;
                    $count = count($posts['full_names']);
                    $group = $this->ion_auth->get_group_by_name('guardian');
                    $groups = array($group->id);
                    
                    foreach ($posts['full_names'] as $i => $value):
                        $count_create++;
                        $full_name = $posts['full_names'][$i];
                        $full_name_array = explode(' ',$full_name);
                          
                        if(count($full_name_array) == 2){
                            $first_name = $full_name_array[0];
                            $last_name = $full_name_array[1];
                        }else{
                            $first_name = $full_name_array[0];
                            $middle_name = isset($full_name_array[1]) ? $full_name_array[1] : '';
                            $last_name_array = array();
                            for($i = 2; $i < count($full_name_array); $i++){
                              $last_name_array[] = $full_name_array[$i].' ';
                            }
                            $last_name = implode(' ',$last_name_array);
                        }
                        $additional_data = array(
                            'first_name' => $first_name,
                            'middle_name' => isset($middle_name)?$middle_name:'',
                            'last_name' => $last_name,
                            'is_validated'=>1,
                            'is_onboarded'=>1,
                            'is_complete_setup'=>1,
                            'email' => isset($posts['emails'][$i]) ? $posts['emails'][$i] : '',
                            'id_number' => isset($posts['id_numbers'][$i]) ? $posts['id_numbers'][$i] : '',
                            'phone' => isset($posts['phones'][$i]) ?  valid_phone($posts['phones'][$i]) : 0,
                            'currency' => 'KES',
                            'is_active' => 1,
                            'activation_code' => rand(1000,9999),
                        );
                        $phone =  isset($posts['phones'][$i]) ? valid_phone($posts['phones'][$i]) : 0;
                        $password = $this->input->post('password');
                        $identity = valid_phone($phone);
                        if($user_id = $this->ion_auth->register($identity,$password,'', $additional_data,$groups,TRUE)){
                            
                        }else{
                          $this->session->set_flashdata('error','Could not create user guardian');
                            //redirect('signup');
                        }
                    endforeach;
                    $parent_id = $posts['parent_id'];
                    $user_options_phone = $this->users_m->get_user_by_phone_options($valid_phones_arr);
                    $guardians_arr = [];
                    $parent = $this->parents_m->get_user_by_parent_user_id($parent_id);
                    foreach ($posts['full_names'] as $i => $value):
                        $phone = valid_phone($posts['phones'][$i]);
                        $user_id = '';
                        if(array_key_exists($phone , $user_options_phone)){
                            $user_id = $user_options_phone[$phone]->id;
                        }
                        $guardians_arr[] = array(
                            'user_id'=>$user_id,
                            'parent_id'=>$parent->parent_id,
                            'user_parent_id'=>$parent->user_parent_id,
                            'created_on'=>time(),
                            'created_by'=>$this->user->id,
                            'active'=>1,
                        );
                    endforeach;
                    if($this->guardians_m->insert_batch($guardians_arr)){
                        $response = array(
                            'status' => 1,
                            'message' => $count_create . ' Users created successesfully',
                            'refer'=>site_url('admin/guardians/listing'),
                        );
                    }else{
                        $response = array(
                            'status' => 0,
                            'message' => 'Could not create guardian try again',
                        );
                    }
                }else{
                    $post = array();
                    $form_errors = $error_messages;
                    foreach ($form_errors as $key => $value) {
                        $post[$key] = $value;
                    }
                    $response = array(
                        'status' => 0,
                        'message' => 'There are some errors on the form. Please review and try again.',
                        'validation_errors' => $post,
                    );
                }
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'You have not submitted any fixtures to process',
            );
        }
        echo json_encode($response);
    }
}