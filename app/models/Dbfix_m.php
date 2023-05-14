<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Dbfix_m extends CI_Model{

	/**
     * The constructor
     * @access public
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this ->load->dbforge();
    }

	function create_table($table_name,$fields = array(),$keys = array()){
		if(!empty($table_name)&&count($fields)){

			//table exists
			if($this->db->table_exists($table_name)){
				//cannot overwrite table

				return FALSE;
			}


			//add id field
			$this->dbforge->add_field('id');

			//add fields
			$this->dbforge->add_field($fields);

			//add keys
			if(count($keys)){
				$this->dbforge->add_keys($keys);
			}

			//create table  if not exists
			$this->dbforge->create_table($table_name, TRUE);

			return TRUE;

		}
		return FALSE;

	}

	function drop_table($table){
		if(!empty($table)){

			//table exists
			if($this->db->table_exists($table)){
				$this->dbforge->drop_table($table);
				return TRUE;
			}
		}
		return FALSE;

	}

	function rename_table($old_table ='',$new_table = ''){
		if(!empty($old_table)&&!empty($new_table)){

			//table exists
			if($this->db->table_exists($old_table)){
				$this->dbforge->rename_table($old_table,$new_table);
				return TRUE;
			}
		}
		return FALSE;

	}

	function add_column($table,$fields = array()){
		if(!empty($table)&&count($fields)){

			//table exists
			if(!$this->db->table_exists($table)){
				return FALSE;
			}

			foreach($fields as $tf=>$st){

				if(!$this->db->field_exists($tf,$table)){
					//create field
					$tff[$tf] =$st;
					$this->dbforge->add_column($table, $tff);
				}
			}
			return TRUE;

		}
		return FALSE;

	}

	function modify_column($table,$fields = array()){
		if(!empty($table)&&count($fields)){

			//table exists
			if(!$this->db->table_exists($table)){
				return FALSE;
			}

			foreach($fields as $tf=>$st){
				if($this->db->field_exists($tf,$table)){
					//modify field
					$tff[$tf] =$st;
					$this->dbforge->modify_column($table, $tff);
				}
			}
			return TRUE;

		}
		return FALSE;

	}

	function drop_column($table,$fields = array()){
		if(!empty($table)&&count($fields)){

			//table exists
			if(!$this->db->table_exists($table)){
				return FALSE;
			}

			foreach($fields as $tf){
				if($this->db->field_exists($tf,$table)){
					//drop field
					$this->dbforge->drop_column($table, $tf);
				}
			}
			return TRUE;

		}
		return FALSE;

	}


}
