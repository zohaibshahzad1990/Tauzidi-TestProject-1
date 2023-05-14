<?php if (!defined('BASEPATH')) exit('No direct script access allowed.');

class MY_Form_validation extends CI_Form_validation
{
	function __construct($rules = array())
	{
		parent::__construct($rules);
	}

	/**
	 * Alpha-numeric with underscores dots and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function alpha_dot_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_\-\.])+$/i", $str)) ? FALSE : TRUE;
	}
	
	/**
	 * Captcha Value validation
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	function captcha($str)
	{
		if(!session_id()){
			session_start();
		}
		return ($_SESSION['captcha']==$str)?TRUE :FALSE;
	}

	/**
	 * Formats an UTF-8 string and removes potential harmful characters
	 *
	 * @access	public
	 * @param	string
	 * @return	string
	 * @author	Jeroen v.d. Gulik
	 * @since	v1.0-beta1
	 * @todo	Find decent regex to check utf-8 strings for harmful characters
	 */
	function utf8($str)
	{
		// If they don't have mbstring enabled (suckers) then we'll have to do with what we got
		if ( ! function_exists('mb_convert_encoding')) ///Fixed the error in Pyro 1.3
		{
			return $str;
		}

		$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');

		return htmlentities($str, ENT_QUOTES, 'UTF-8');
	}

	function valid_phone($phone=0)
	{
		if(preg_match("/^[\+0-9\-\(\)\s]*$/", $phone)) 
		{
			if(strlen($phone)<14 && strlen($phone)>8)
			{
				return  $phone;
			}
			else
			{
				return (bool) filter_var($phone, FILTER_VALIDATE_EMAIL);
				//$this->my_form_validation->set_message('valid_phone','User Name field is required.');
				return FALSE;
			}
		}
		else
		{
			return (bool) filter_var($phone, FILTER_VALIDATE_EMAIL);
			//$this->my_form_validation->set_message('valid_phone','User Name field is required.');
			return FALSE;
		}
	}

	function valid_identity($identity=''){
		if(valid_email($identity) || valid_phone($identity)){
			return $identity;
		}else{
			return (bool) filter_var($identity, FILTER_VALIDATE_EMAIL);
			return FALSE;
		}
	}

	function currency($str){
	    if(preg_match('/^-?[0-9,.]+$/', $str)){
	    	return str_replace(',','',$str);
	    }else{
	    	return FALSE;
	    }
	}

	function date($date=0){
		if($date){
			$new_date = strtotime($date);
			if($new_date){
				return $new_date;
			}else{
				if(is_numeric($date)){
					return $date;
				}else{
					return FALSE;
				}
			}
		}else{
			return FALSE;
		}
	}

	function xss_clean($str=''){
		return  filter_var(htmlentities($str), FILTER_SANITIZE_STRING);
	}

	
}

/* End of file MY_Form_validation.php */