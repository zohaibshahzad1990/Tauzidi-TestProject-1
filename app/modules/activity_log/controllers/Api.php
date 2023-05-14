<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

  public $response = array(
    'result_code' => 0,
    'result_description' => 'Default Response'
  );

  function __construct(){
        parent::__construct();
        $this->load->model('activity_log_m');
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

    public function get_activity_logs(){
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $response = array();
        foreach ($this->data as $key => $value) {
            if(preg_match('/phone/', $key)){
                $_POST[$key] = valid_phone($value);
            }else{
                $_POST[$key] = $value;
            }
        }
        $field_name = '';
        $sort_order = '';
        $sort_field = '';
        $sort_role = 0;
        $search_field = '';
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 20;
        $filter_parameters = array();
        if (isset($this->filter_params)) {
            $update = $this->filter_params;
            if(isset($update->filter)){
                $search_field = $update->filter;
            }          
            if(isset($update->sortOrder)){                    
                $sort_order = $update->sortOrder;
            }
            if(isset($update->sortField)){
                $sort_field = $update->sortField;
            }

            if(isset($update->pageNumber)){
                $page_number = $update->pageNumber;
            }
            if(isset($update->pageSize)){
                $page_size = $update->pageSize;
            }
            if(isset($update->options->sortRole)){
                $sort_role = $update->options->sortRole;
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;

            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "sort_field"=>$sort_field,
            );       
        } 
        $total_rows = $this->activity_log_m->count_all_activty_logs($filter_parameters);
        $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
        $posts = $this->activity_log_m->limit($pagination['limit'])->get_activty_logs($filter_parameters);
        $activity_array = array();
        if($posts){
            $count = $start+1;
            $_new = 0;
            foreach ($posts as $key => $post):
                $activity_array[] = (object) array(
                    'id'=> $count++,
                    '_id'=>$post->id,
                    'url' =>$post->url,
                    'description' =>$post->description,
                    'action' =>$post->action,
                    //'active' =>$post->active,
                    'created_by' =>$post->created_by,
                    'created_on'=>$post->created_on
                );
            endforeach;
            $response = array(
                'status'=>1,
                'itemCount'=> $total_rows,
                'items'=> $activity_array
            );
        }else{
            $response = array(
                'status'=>0,
                'itemCount'=> 0,
                'items'=> array()
            );
        }
        echo json_encode($response);
    }

}