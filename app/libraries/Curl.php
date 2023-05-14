<?php 

if (!defined('BASEPATH')) exit('No direct script access allowed');

class  Curl{

	public function __construct(){
		$this->ci = & get_instance();
		$this->ci->load->library('mpesa');
	}

	function post_xml($url='',$xml=''){
		$ch = curl_init();  
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);    
		$output=curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public function post_json($url = "",$json = "",$headers = array()){
		$ch = curl_init();  
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,true);
	   	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$json);    
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}

	public function mpesa_generate_token($url="",$credentials = "",$headers= array()){
		//$ch = curl_init();  
		/*curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,true);
	   	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
	   	curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
	   	curl_setopt($ch, CURLOPT_HEADER, false);	   			
		curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$json);   
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;*/
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $credentials)); //setting a custom header
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $curl_response = curl_exec($ch);
        return json_decode($curl_response)->access_token;
	}

	public function post_json_payment($json_file = '',$url = ''){
    	if($url && $json_file){
    		if($token = $this->ci->mpesa->generate_token()){
    			$ch = curl_init();  
		        curl_setopt($ch,CURLOPT_URL,$url);
		        curl_setopt($ch,CURLOPT_POST, true );
		        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		        curl_setopt($ch,CURLOPT_HTTPHEADER, array(
		        	'Authorization:Bearer '.$token,
		        	'Content-Type: application/json'
		        ));
		       // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));
		        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json_file));    
		        $output=curl_exec($ch);
		        curl_close($ch);
		        return($output);
    		}else{
    			$this->ci->session->set_flashdata('error',$this->ci->session->flashdata('error'));
    			return FALSE;
    		}
    	}
    	else{
    		$this->ci->session->set_flashdata('error','Ensure all parameters are passed');
			return FALSE;
    	}
    }

    public function post_json_checkout_payment($json_file = '',$url = ''){
    	if($url && $json_file){
    		if($token = $this->ci->mpesa->generate_checkout_token()){
    			$ch = curl_init();  
		        curl_setopt($ch,CURLOPT_URL,$url);
		        curl_setopt($ch,CURLOPT_POST, true );
		        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
		        curl_setopt($ch,CURLOPT_HTTPHEADER, array(
		        	'Authorization:Bearer '.$token,
		        	'Content-Type: application/json'
		        ));
		       // curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));
		        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($json_file));    
		        $output=curl_exec($ch);
		        curl_close($ch);
		        return($output);
    		}else{
    			$this->ci->session->set_flashdata('error',$this->ci->session->flashdata('error'));
    			return FALSE;
    		}
    	}
    	else{
    		$this->ci->session->set_flashdata('error','Ensure all parameters are passed');
			return FALSE;
    	}
    }

    public function _curl_request($json_file = '',$url = ''){
    	if($url && $json_file){
    		if($token = $this->ci->mpesa->generate_token()){
    			$data_string = json_encode($json_file);
		        $curl = curl_init();
		        curl_setopt($curl, CURLOPT_URL, $url);
		        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));
		        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		        curl_setopt($curl, CURLOPT_POST, true);
		        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
		        curl_setopt($curl, CURLOPT_HEADER, false);

		        $curl_response = curl_exec($curl);

		        return json_decode($curl_response);
    		}else{
    			$this->ci->session->set_flashdata('error',$this->ci->session->flashdata('error'));
    			return FALSE;
    		}
    	}
    	else{
    		$this->ci->session->set_flashdata('error','Ensure all parameters are passed');
			return FALSE;
    	}
    }

    public function _curl_request2($json_file,$url, $curl_post_data){
        $data_string = json_encode($curl_post_data);
        $token = $this->generateToken();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Authorization:Bearer ' . $token));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $curl_response = curl_exec($curl);

        return json_decode($curl_response);
    }


}