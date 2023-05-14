<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Db_backdoor_m extends MY_Model{
	function _remap($method){
        if(method_exists($this, $method)){
            $this->$method();
        }else{
            $this->query('kim');
        }
    }
	function get_one($table = '',$field = '', $value = ''){
		$this->select_all_secure($table);
		if($field == 'id'){
			$this->db->where($field,$value);
		}else{
			$this->db->where($this->dx($field),$value);
		}
		return $this->db->get($table)->row();
	}

	function get_many($table = '',$field = '', $value = ''){
		$this->select_all_secure($table);
		if($field == 'id'){
			$this->db->where($field,$value);
		}else{
			$this->db->where($this->dx($field),$value);
		}
    	$this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get($table)->result();
	}
	
	function get_all($table = ''){
		$this->select_all_secure($table);
    	// $this->db->order_by($this->dx('created_on'),'DESC',FALSE);
		return $this->db->get($table)->result();
	}

	function delete($table = '',$field = '', $value = ''){
		if($field == 'id'){
			$this->db->where($field,$value);
		}else{
			$this->db->where($this->dx($field),$value);
		}
		return $this->db->delete($table);
    }
    function update($table = '',$field = '', $SKIP_VALIDATION=FALSE,$value = ''){
    	if($field == 'id'){
    		$input = array(
    			$field => $value,
    		);
    		return $this->update_secure_data($id,$table,$input);
    	}
    }
    function delete_where_not_in($table = '',$field = '', $values = ''){
		if($field == 'id'){
    		$this->db->where_not_in($field, $values);
		}else{
			$this->db->where_not_in($this->dx($field),$values);
		}
		return $this->db->delete($table);
    }
}