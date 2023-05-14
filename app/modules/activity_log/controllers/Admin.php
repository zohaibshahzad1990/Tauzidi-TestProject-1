<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{

	protected $data = array();

	function __construct(){
        parent::__construct();
        $this->load->model('activity_log_m');
    }


    function log(){
        $first_name = $this->input->post_get('first_name');
        $phone = $this->input->post_get('phone');
        $filter_parameters = array();
        if($this->input->post_get('filter') == 'filter'){
            /*$filter_parameters = array(
                'first_name' => $first_name,
                'phone' =>$phone
            );*/
        }
    	$total_rows = $this ->activity_log_m->count_all_activty_logs($filter_parameters);
        $pagination = create_pagination('admin/activity_log/log/pages', $total_rows,50,5,TRUE);
        $posts = $this->activity_log_m->limit($pagination['limit'])->get_activty_logs($filter_parameters);
        $user_ids = [];
        foreach ($posts as $key => $post) {
            if($post->user_id){
                $user_ids[] = $post->user_id;
            }
        }
        $this->data['user_options'] = $this->users_m->get_user_array_options($user_ids);
        $this->data['posts'] = $posts;
        $this->data['pagination'] = $pagination;
    	$this->template->title('Activity Log')->build('admin/listing',$this->data);
    }

}?>