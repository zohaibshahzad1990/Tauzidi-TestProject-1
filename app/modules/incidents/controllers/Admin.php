<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{

	function __construct(){
        parent::__construct();
        $this->load->model('incidents_m');  
    }

    function listing(){
    	$total_rows = $this->incidents_m->count_my_active_incidents();
        $pagination = create_pagination('admin/incidents/listing/pages',$total_rows,10,5,TRUE);
        //print_r($pagination); die();
        $posts = $this->incidents_m->limit($pagination['limit'])->get_all_active_unresolved();
        $user_ids = [];
        foreach ($posts as $key => $post) {
        	$user_ids[] = $post->reported_by;
        }
        $this->data['posts'] = $posts;
        $this->data['users'] = $this->users_m->get_user_array_options($user_ids);
        $this->data['pagination'] = $pagination;
    	$this->template->title('Incidents Listing')->build('admin/listing',$this->data);
    }

    function history(){
    	$total_rows = $this->incidents_m->count_my_active_resolved_incidents();
        $pagination = create_pagination('admin/incidents/history/pages',$total_rows,10,5,TRUE);
        //print_r($pagination); die();
        $posts = $this->incidents_m->limit($pagination['limit'])->get_all_active_resolved();
        $user_ids = [];
        foreach ($posts as $key => $post) {
        	$user_ids[] = $post->reported_by;
        }
        $this->data['posts'] = $posts;
        $this->data['users'] = $this->users_m->get_user_array_options($user_ids);
        $this->data['pagination'] = $pagination;
    	$this->template->title('Resolved Incidents History')->build('admin/resolved',$this->data);
    }

} ?>