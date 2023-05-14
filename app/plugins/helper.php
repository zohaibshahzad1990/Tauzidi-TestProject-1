<?php defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Session Plugin
 *
 * Read and write session data
 *
 * @package		PyroCMS
 * @author		PyroCMS Dev Team
 * @copyright	Copyright (c) 2008 - 2011, PyroCMS
 *
 */
class Plugin_Helper extends Plugin
{
	/**
	 * Data
	 *
	 * Loads a theme partial
	 *
	 * Usage:
	 * {pyro:helper:lang line="foo"}
	 *
	 * @param	array
	 * @return	array
	 */
	public function _construct(){
		$this->load->helper('text');
		
	}
	public function lang()
	{
		$line = $this->attribute('line');
		return $this->lang->line($line);
	}

	public function date()
	{
        $this->load->helper('date');

		$format		= $this->attribute('format');
		$timestamp	= $this->attribute('timestamp');

		return $timestamp ? date($format,$timestamp) : date($format,time());
	}
	
	public function number()
	{

		$value		= $this->attribute('value',0);
		$dec	= $this->attribute('dec',2);
		if(!is_numeric($value)) $value=0;

		return number_format($value,$dec);
	}
	
	public function trim_words()
	{

		$txt		= $this->attribute('text','');
		$len		= $this->attribute('length',200);
		$dash='';
		if(strlen($txt)>$len){
			$dash=' ...';
		}

		return character_limiter($txt,$len).$dash;
	}
	
	public function page()
	{
		$page_id= $this->attribute('page_id',10);
		$this->db->where('id',$page_id);
		$row = $this->db->get('pages')->result_array();
		return $row;
	}

	public function gravatar()
	{
		$email		= $this->attribute('email', '');
		$size		= $this->attribute('size', '50');
		$rating		= $this->attribute('rating', 'g');
																	//deprecated
		$url_only	= (bool) in_array($this->attribute('url-only', $this->attribute('url_only', 'false')), array('1', 'y', 'yes', 'true'));

		return gravatar($email, $size, $rating, $url_only);
	}

	public function strip()
	{
		return preg_replace('!\s+!', $this->attribute('replace', ' '), $this->content());
	}
	
	public function messages()
	{
		if(function_exists('validation_errors')){
			$d=validation_errors();
			return $d;//validation_errors();
		}
		return '';
	}
}

/* End of file theme.php */