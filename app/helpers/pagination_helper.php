<?php defined('BASEPATH') OR exit('No direct script access allowed.');

/**
  * The Pagination helper cuts out some of the bumf of normal pagination with the Paging Meta of To and From Added to the array
  * @author		Philip Sturgeon
  * @filename	pagination_helper.php
  * @title		Pagination Helper
  * @version	1.0
 **/

function create_pagination($uri, $total_rows, $limit = NULL, $uri_segment = 4, $full_tag_wrap = TRUE)
{
	$ci =& get_instance();
	$ci->load->library('pagination');

	$current_page = $ci->uri->segment($uri_segment, 0);
	$url_suffix = '';
	if(isset($_GET)){
		$get_values = $_GET;
		$count = count($get_values);
		$i = 1;
		foreach($get_values as $k => $v){
			if($count==1){
				$url_suffix.='?'.$k.'='.$v;
			}else{
				if($i==1)
				{
					if(is_array($v)){
						$url_suffix.='?'.$k.'='.http_build_query($v);
					}else{
						$url_suffix.='?'.$k.'='.$v;
					}
				}else
				{
					if(is_array($v)){

						foreach ($v as $key => $value) {
							# code...
							$url_suffix.='&'.$k.'[]='.$value.'';
						}

					}else{
						$url_suffix.='&'.$k.'='.$v.'';
					}
				}
			}
			$i++;
		}
	}
	if($limit){
		$limit = $limit;
	}else{
		if(preg_match('/admin/',$ci->uri->segment(1))){
			$limit = 100;
		}
		else
		{
			$limit = 50;
		}
	}
	 
	//echo $url_suffix;
	//die;
	// Initialize pagination
	$config['suffix']				= $ci->config->item('url_suffix').$url_suffix;
	$config['base_url']				= $config['suffix'] !== FALSE ? rtrim(site_url($uri), $config['suffix']) : site_url($uri);
	$config['total_rows']			= $total_rows; // count all records
	$config['per_page']				= $limit === NULL ? 25 : $limit;
	$config['uri_segment']			= $uri_segment;
	
	$config['page_query_string']	= FALSE;
	$config['num_links'] = 4;
	$config['full_tag_open'] = '<div class="kt-pagination kt-pagination--sm kt-pagination--danger"><ul class="kt-pagination__links">';
	$config['first_link'] = '<i class="fa fa-angle-double-left kt-font-danger"></i>';
	$config['last_link'] = '<i class="fa fa-angle-double-left kt-font-danger"></i>';
	$config['next_link'] = '<i class="fa fa-angle-right kt-font-danger"></i>';
	$config['prev_link'] = '<i class="fa fa-angle-double-left kt-font-danger"></i>';
	$config['cur_tag_open'] = '<li class="kt-pagination__link--active"><a href="" class="active">';
	$config['cur_tag_close'] = '</a></li>';
	$config['num_tag_open'] = '<li>';
	$config['num_tag_close'] = '</li>';
	$config['prev_tag_open'] = '<li class="kt-pagination__link--prev">';
	$config['prev_tag_close'] = '</li>';
	$config['first_tag_open'] = '<li class="kt-pagination__link--first">';
	$config['first_tag_close'] = '</li>';
	$config['last_tag_open'] = '<li class="kt-pagination__link--last">';
	$config['last_tag_close'] = '</li>';
	$config['next_tag_open'] = '<li class="kt-pagination__link--next">';
	$config['next_tag_close'] = '</li>';
	$config['full_tag_close'] = '</ul></div>';
	$ci->pagination->initialize($config); // initialize pagination
	$to_page=$current_page+$config['per_page'];
	if($to_page>$total_rows) $to_page=$total_rows;
	
	return array(
		'total' 	=> $total_rows,
		'to' 	=> $to_page,
		'from' 	=> ($total_rows>0)?($current_page+1):0,
		'current_page' 	=> $current_page,
		'per_page' 		=> $config['per_page'],
		'limit'			=> array($config['per_page'], $current_page),
		'links' 		=> $ci->pagination->create_links($full_tag_wrap)
	);
}

function create_custom_pagination($uri, $total_rows, $limit = NULL, $uri_segment = 4, $full_tag_wrap = TRUE){
	$ci =& get_instance();
	$ci->load->library('pagination');

	$current_page = $uri_segment;
	$url_suffix = '';
	if(isset($_GET)&&count($_GET)){
		$url_suffix = '?'.$_SERVER['QUERY_STRING'];
	}

	if($limit){
		$limit = $limit;
	}else{
		if(preg_match('/admin/',$ci->uri->segment(1))){
			$limit = 100;
		}
		else
		{
			$limit = 50;
		}
	}
	 

	// Initialize pagination
	$config['suffix']				= $ci->config->item('url_suffix').$url_suffix;
	//$config['base_url']				= $config['suffix'] !== FALSE ? rtrim(site_url($uri), $config['suffix']) : site_url($uri);
	$config['base_url']				= site_url($uri);
	$config['total_rows']			= $total_rows; // count all records
	$config['per_page']				= $limit === NULL ? 25 : $limit;
	$config['uri_segment']			= $uri_segment;
	
	$config['page_query_string']	= FALSE;
	$config['num_links'] = 4;
	$config['full_tag_open'] = '<div class="pagination"><ul class="pagination">';
	$config['first_link'] = '&laquo; First';
	$config['last_link'] = 'Last &raquo;';
	$config['next_link'] = 'Next &rsaquo;';
	$config['prev_link'] = '&lsaquo; Prev';
	$config['cur_tag_open'] = '<li><a href="" class="active">';
	$config['cur_tag_close'] = '</a></li>';
	$config['num_tag_open'] = '<li>';
	$config['num_tag_close'] = '</li>';
	$config['prev_tag_open'] = '<li class="prev">';
	$config['prev_tag_close'] = '</li>';
	$config['first_tag_open'] = '<li class="first">';
	$config['first_tag_close'] = '</li>';
	$config['last_tag_open'] = '<li class="last">';
	$config['last_tag_close'] = '</li>';
	$config['next_tag_open'] = '<li class="next">';
	$config['next_tag_close'] = '</li>';
	$config['full_tag_close'] = '</ul></div>';
	$ci->pagination->initialize($config); // initialize pagination
	$to_page=$current_page+$config['per_page'];
	if($to_page>$total_rows) $to_page=$total_rows;
	
	return array(
		'total' 	=> $total_rows,
		'to' 	=> $to_page,
		'from' 	=> ($total_rows>0)?($current_page+1):0,
		'current_page' 	=> $current_page,
		'per_page' 		=> $config['per_page'],
		'limit'			=> array($config['per_page'], $current_page),
		//'links' 		=> $ci->pagination->create_links($full_tag_wrap)
	);
}