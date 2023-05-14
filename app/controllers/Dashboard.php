<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Dashboard extends Api_Controller{

	public $response = array(
		'result_code' => 0,
		'result_description' => 'Default Response'
	);

    private $resource_type_ids = array(1,2,3);

	function __construct(){
        parent::__construct();
        $this->load->model('user_groups/user_groups_m');
        $this->load->model('activity_log/activity_log_m');
    }

    public function _remap($method, $params = array()){
        if(method_exists($this, $method)){
            return call_user_func_array(array($this, $method), $params);
        }
       $this->output->set_status_header('404');
       header('Content-Type: application/json');
       $file = file_get_contents('php://input')?(array)json_decode(file_get_contents('php://input')):array();
       echo json_encode(
        array(
            'status' =>  404,
            'message' =>  'The endpoint cannot be found: '.$this->uri->uri_string(),
        ));
    }

    public function dashboard_statistics(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $stats = $this->resources_m->get_resource_dashboard_statistics();
        if($stats){
            $dashboard_statistics = array();
            foreach ($this->resource_type_ids as $key => $id) {
                if(array_key_exists($id, $stats)){
                    $dashboard_statistics[$id] = $stats[$id];
                }else{
                   $dashboard_statistics[$id] = 0; 
                }
            }
            $response = array(
                'status' => 1,
                'dashboard' => $dashboard_statistics,
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'There are no resources uploaded',
            );
        }        
        echo json_encode($response);
    }

    public function get_recent_users(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $users = $this->users_m->get_new_users();
        if($users){
            $user_array = array();
            foreach ($users as $key => $user) {
                $user_array[] = array(
                    '_id'=> $user->id,
                    'name'=> $user->first_name .' '. $user->last_name,
                    'email'=> $user->email,
                );
            }
            $response = array(
                'status' => 1,
                'users' => $user_array,
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'There are no resources uploaded',
            );
        }        
        echo json_encode($response);
    }

    public function get_recent_payments(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $deposits = $this->deposits_m->get_new_deposits();
        if($deposits){
            $deposit_array = array();
            foreach ($deposits as $key => $deposit) {
                $deposit_array[] = array(
                    '_id'=> $deposit->id,
                    'method'=> 'M-PESA',
                    'amount'=> $deposit->amount,
                    'transaction_id'=> $deposit->transaction_id,
                );
            }
            $response = array(
                'status' => 1,
                'payments' => $deposit_array,
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'There are no recent deposits',
            );
        }        
        echo json_encode($response);
    }

    public function system_visits(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $activities = $this->activity_log_m->get_latest_activity_log();
        if(TRUE){
            $logins = $this->activity_log_m->get_latest_logins_log();
            $logins_array = array();
            $visits_log = array();
            $months_array = array();
            for ($i=0; $i < 12 ; $i++) { 
                $months_array[date('M Y',strtotime("-".$i." month"))] = date('M Y',strtotime("-".$i." month"));
            }
            foreach ($logins as $key => $login) {
                $logins_array[date('M Y',$login->created_on)] = intval($login->count);
            }
            $visits_log = array();
            foreach ($activities as $key => $activity) {
                $visits_log[date('M Y',$activity->created_on)] = intval($activity->count);
            }
            //print_r($visits_log); die();
            $logins_array_final = array();
            $visits_array_log = array();
            foreach ($months_array as $key => $month) {
                $logins_array_final[$month] = isset($logins_array[$month])?$logins_array[$month]:0;
                $visits_array_log[$month] = isset($visits_log[$month])?$visits_log[$month]:0;
            }
            $response = array(
                'status' => 1,
                'portal_visits' => array_reverse($visits_array_log),
                'logins' => array_reverse($logins_array_final)
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'There are no activities',
            );
        }        
        echo json_encode($response);
    }

    public function resource_reading_by_months(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $activities = $this->students_m->get_most_read_resource();
        if($activities){
            //$users_ = $this->students_m->get_most_read_resource();
            $activity_array = array();
            $resource_ids = array();
            $months_array = array();
            for ($i=0; $i < 12 ; $i++) { 
                $months_array[date('M Y',strtotime("-".$i." month"))] = date('M Y',strtotime("-".$i." month"));
            }
            foreach ($activities as $key => $activity) {
                $activity_array[date('M Y',$activity->created_on)] = intval($activity->count);
            }

            //print_r($activity_array); die();
            //print_r($visits_log); die();
            $activity_array_final = array();
            $visits_array_log = array();
            foreach ($months_array as $key => $month) {
                $activity_array_final[$month] = isset($activity_array[$month])?$activity_array[$month]:0;
            }
            $response = array(
                'status' => 1,
                'resource_per_month' => array_reverse($activity_array_final),
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'There are no activities',
            );
        }        
        echo json_encode($response);
    }

    public function popular_resource(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $most_popular = $this->resources_m->get_most_popular_resource();
        if($most_popular){
            $most_popular_array = array();
            $count = 1;
            foreach ($most_popular as $key => $most) {
                $most_popular_array[] = array(
                    "_id"=>$count++,
                    'id'=>intval($most->id),
                    'file_type'=>$most->file_type,
                    'file_size'=>$most->file_size,
                    'views'=>intval($most->views),
                    'name'=>$most->name
                ); 
            }
            $response = array(
                'status' => 1,
                'popular' => $most_popular_array,
            );
        }else{
            $response = array(
                'status' => 0,
                'message' => 'There are no activities',
            );
        }        
        echo json_encode($response);
    }

}