<?php if(!defined('BASEPATH')) exit('You are not allowed to view this script');
class Api extends Api_Controller{

    public $response = array(
        'result_code' => 0,
        'result_description' => 'Default Response'
    );

    function __construct(){
        parent::__construct();
        $this->load->model('invoices_m');
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

    public function get_my_invoices(){
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
        $filter_type = '';
        $filter_payment_type = '';
        $filter_public_status = '';
        $page_number = 0;
        $page_size = 10;
        $start = 0;
        $end = 10;
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
            if(isset($update->options->filterType)){
                $filter_type = $update->options->filterType;
                $filter_payment_type = $update->options->filterPaymentType;
                $filter_public_status = $update->options->filterPublicStatusType;
            }
            $start = $page_number * $page_size;
            $end = $start + $page_size;

            $filter_parameters = array(
                "search_fields"=>$search_field,
                "sort_order"=>$sort_order,
                "sort_field"=>$sort_field,
                "filter_type"=>$filter_type,
                "filter_payment_type"=>$filter_payment_type,
                "filter_public_status"=>$filter_public_status,
            );      
        } 
        if($this->user){
            $total_rows = $this->invoices_m->count_user_invoices($this->user->id,$filter_parameters);
            $pagination = create_custom_pagination('api',$total_rows,$page_size,$start,TRUE);
            $posts = $this->invoices_m->limit($pagination['limit'])->get_user_invoices($this->user->id,$filter_parameters);
            if($posts){
                $invoice_array = array();
                $count = 0;
                foreach ($posts as $key => $post):
                    if($post->type == 1){
                        $type = "Subscription Invoice";
                    }else if($post->type == 1){
                        $type = "Subscription Payment";
                    }
                    $invoice_array[]= array(
                        'id'=> $count++,
                        '_id'=>$post->id,
                        'type'=>$type,
                        'invoice_number'=>$post->invoice_number,
                        'amount_payable'=>$post->amount_payable,
                        'amount_paid' =>$post->amount_paid,
                        'description' =>$post->description,
                        'created_on' =>$post->created_on

                    );
                endforeach;
                $response = array(
                    'itemCount' => count($invoice_array),
                    'items' => $invoice_array
                );
            }else{
                $response = array(
                    'itemCount' => 0,
                    'items' => array()
                );
            }
        }else{
            $response = array(
                'status' => 0,
                'message' => 'User details missing'
            );
        } 
        echo json_encode($response);
    }
}