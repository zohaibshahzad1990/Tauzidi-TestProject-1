<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Menus_m extends MY_Model{

	protected $_table = 'menus';

	protected $special_url_segments = array("/verify_ownership/","/edit/","/view/","/listing/",'/view_installments/',"/create/","/miscellaneous_statement/","/fine_statement/","/top_up/","/sell/","/connect/","/statement/");

	protected $notification_counts = array();

	protected $parent_id_as_key_link_as_value_multidimensional_array = array();

	protected $child_id_as_key_parent_id_as_value_array = array();

	protected $link_as_key_parent_id_as_value_array = array();

	protected $parent_id_as_key_menu_as_value_array = array();

	public function __construct(){
		parent::__construct();
		$this->load->dbforge();
		$this->install();
		$this->parent_id_as_key_link_as_value_multidimensional_array = $this->get_parent_id_as_key_url_as_value_multidimensional_array();
		$this->active_parent_id_as_key_link_as_value_multidimensional_array = $this->get_active_parent_id_as_key_url_as_value_multidimensional_array();
		$this->child_id_as_key_parent_id_as_value_array = $this->get_child_id_as_key_parent_id_as_value_array();
		$this->active_child_id_as_key_parent_id_as_value_array = $this->get_active_child_id_as_key_parent_id_as_value_array();
		$this->link_as_key_parent_id_as_value_array = $this->get_link_as_key_parent_id_as_value_array();
		$this->active_link_as_key_parent_id_as_value_array = $this->get_active_link_as_key_parent_id_as_value_array();
		$this->parent_id_as_key_menu_as_array = $this->get_parent_id_as_key_menu_as_value_array();
		$this->active_parent_id_as_key_menu_as_array = $this->get_active_parent_id_as_key_menu_as_value_array();
	}
	
	public function install(){
		$this->db->query("
			create table if not exists menus(
			id int not null auto_increment primary key,
			`parent_id` varchar(200),
			`name` varchar(200),
			`url` varchar(200),
			`help_url` varchar(200),
			`icon` varchar(200),
			`color` varchar(200),
			`size` varchar(200),
			`position` varchar(200),
			`active` varchar(200),
			`contextual_help_content` varchar(200),
			created_by varchar(200),
			created_on varchar(200),
			modified_on varchar(200),
			modified_by varchar(200)
		)");
	}

	public function get_parent_id_as_key_url_as_value_multidimensional_array(){
		$arr = array();
		$this->db->select(
			array(
				$this->dx('parent_id')." as parent_id ",
				$this->dx('url')." as url ",
			)
		);
		//$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->parent_id][] = $menu->url;
		endforeach;
		return $arr;
	}

	public function get_active_parent_id_as_key_url_as_value_multidimensional_array(){
		$arr = array();
		$this->db->select(
			array(
				$this->dx('parent_id')." as parent_id ",
				$this->dx('url')." as url ",
			)
		);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->parent_id][] = $menu->url;
		endforeach;
		return $arr;
	}

	public function get_child_id_as_key_parent_id_as_value_array(){
		$arr = array();
		$this->db->select(
			array(
				$this->dx('parent_id')." as parent_id ",
				" id ",
			)
		);
		//$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->id] = $menu->parent_id;
		endforeach;
		return $arr;
	}

	public function get_active_child_id_as_key_parent_id_as_value_array(){
		$arr = array();
		$this->db->select(
			array(
				$this->dx('parent_id')." as parent_id ",
				" id ",
			)
		);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->id] = $menu->parent_id;
		endforeach;
		return $arr;
	}

	public function get_link_as_key_parent_id_as_value_array(){
		$arr = array();
		$this->db->select(
			array(
				$this->dx('url')." as url ",
				$this->dx('parent_id')." as parent_id ",
			)
		);
		//$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->url] = $menu->parent_id;
		endforeach;
		return $arr;
	}

	public function get_active_link_as_key_parent_id_as_value_array(){
		$arr = array();
		$this->db->select(
			array(
				$this->dx('url')." as url ",
				$this->dx('parent_id')." as parent_id ",
			)
		);
		$this->db->where($this->dx('active')." = '1' ",NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->url] = $menu->parent_id;
		endforeach;
		return $arr;
	}

	public function get_parent_id_as_key_menu_as_value_array(){
		$arr = array();
		$this->select_all_secure('menus');
		//$this->db->where($this->db->where('active').' = "1" ',NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->parent_id][] = $menu;
		endforeach;
		return $arr;
	}

	public function get_active_parent_id_as_key_menu_as_value_array(){
		$arr = array();
		$this->select_all_secure('menus');
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu):
			$arr[$menu->parent_id][] = $menu;
		endforeach;
		return $arr;
	}

	public function get($id = 0){
		$this->select_all_secure('menus');
		$this->db->where('id',$id);
		return $this->db->get('menus')->row();
	}

	function insert($input = array(),$skip_value = FALSE){
		return $this->insert_secure_data('menus',$input);
	}

	function generate_page_title($url=''){
		if(empty($url)){
			$url = $this->uri->uri_string();
		}
		$this->select_all_secure('menus');
		$this->db->where($this->dx('url').'="'.$this->db->escape_str($url).'"',NULL,FALSE);
		$menu = $this->db->get('menus')->row();
		if($menu){
			//echo '<i class="'.$menu->icon.' font-dark"></i> ';
			$name = $menu->name;
			foreach ($this->notification_counts as $k => $v){
				$name = preg_replace('/\['.$k.'\]/', '', $menu->name);
				if($name!==$menu->name){
					break;
				}
			}
			echo '<span class="caption-subject font-dark">'.ucwords($name).'</span>';
		}else{
			echo '<!--<i class="fa fa-list-ul font-dark"></i>-->
			<span class="caption-subject font-dark">{metronic:template:title}</span>';
		}
	}

	function generate_page_well_nav($url=''){
		if(empty($url)){
			$url = $this->uri->uri_string();
		}
		$_path='';
		if($url != 'admin'){
			$links = explode("/",$url);
			$_segment = $this->uri->segment(sizeof($links));
			$this->select_all_secure('menus');
			$this->db->where($this->dx('url').'="'.$this->db->escape_str($url).'"',NULL,FALSE);
			$menu = $this->db->get('menus')->row();
			if($menu){
				//$icn = '<i class="'.$menu->icon.' font-dark"></i>';
				$_path.="
				<ul class='m-subheader__breadcrumbs m-nav m-nav--inline'>
				<li class='m-nav__item m-nav__item--home'>
				<a href='".site_url('/admin')."' class='m-nav__link m-nav__link--icon'>
				<i class='m-nav__link-icon la la-home'></i> Home
				</a>
				</li>
				
				<li class='m-nav__separator'>/</li>

				<li class='m-nav__item'>
				<span class='m-nav__link-text'>".$menu->name."</span>
				</li>
				</ul>
				";
			}
			else{
				$_path.="
				<ul class='m-subheader__breadcrumbs m-nav m-nav--inline'>
				<li class='m-nav__item m-nav__item--home'>
				<a href='".site_url('/admin')."' class='m-nav__link m-nav__link--icon'>
				<i class='m-nav__link-icon la la-home'></i> Dashboard Home
				</a>
				</li>
				</ul>
				";
			}
		}
		else{
			$_path.="
			<ul class='m-subheader__breadcrumbs m-nav m-nav--inline'>
			<li class='m-nav__item m-nav__item--home'>
			<i class='m-nav__link-icon la la-home'></i> 
			</li>
			</ul>
			";
		}
		echo $_path;
	}

	function update($id,$input,$val=FALSE){
		return $this->update_secure_data($id,'menus',$input);
	}

	function get_all(){
		$this->select_all_secure('menus');
		return $this->db->get('menus')->result();
	}

	function count_all_active(){
		return $this->db->count_all_results('menus');
	}

	function get_options(){
		$arr = array();
		$this->select_all_secure('menus');
		$menus = $this->db->get('menus')->result();
		foreach($menus as $menu){
			$arr[$menu->id] = $menu->name;
		}
		return $arr;
	}

	function get_parent_links(){
		$this->select_all_secure('menus');
		$this->db->where($this->dx('parent_id').' = "0" ',NULL,FALSE);
		$this->db->order_by($this->dx('position').'+0','ASC', FALSE);
		return $this->db->get('menus')->result();
	}

	function get_active_parent_links(){
		$this->select_all_secure('menus');
		$this->db->where($this->dx('parent_id').' = "0" ',NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$this->db->order_by($this->dx('position').'+0','ASC', FALSE);
		return $this->db->get('menus')->result();
	}

	function get_active_links_in_array($menus_array=array()){
		$menu_id_list = '0';
		$count = 1;
		foreach($menus_array as $menu_id){
			if($menu_id){
				if($count==1){
					$menu_id_list = $menu_id;
				}else{
					$menu_id_list .= ','.$menu_id;
				}
				$count++;
			}
		}
		$this->select_all_secure('menus');
		$this->db->where($this->dx('parent_id').' = "0" ',NULL,FALSE);
		if($menu_id_list){
			$this->db->where('id IN ('.$menu_id_list.')',NULL,FALSE);
		}
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$this->db->order_by($this->dx('position').'+0','ASC', FALSE);
		return $this->db->get('menus')->result();
	}

	function get_active_parent_link($parent_id = 0){
		$this->select_all_secure('menus');
		$this->db->where('id',$parent_id);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$this->db->order_by($this->dx('position').'+0','ASC', FALSE);
		return $this->db->get('menus')->row();
	}

	function get_children_links($parent_id = 0){
		if(isset($this->parent_id_as_key_menu_as_array[$parent_id])){
			return $this->parent_id_as_key_menu_as_array[$parent_id];
		}else{
			return FALSE;
		}
	}

	function get_active_children_links($parent_id = 0){
		if(isset($this->active_parent_id_as_key_menu_as_array[$parent_id])){
			return $this->active_parent_id_as_key_menu_as_array[$parent_id];
		}else{
			return FALSE;
		}
	}

	function has_children($parent_id = 0){
		if(in_array($parent_id,$this->child_id_as_key_parent_id_as_value_array)){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	function has_active_children($parent_id = 0){
		if(in_array($parent_id,$this->active_child_id_as_key_parent_id_as_value_array)){
			return TRUE;
		}else{
			return FALSE;
		}
	}

	function has_active_grand_children($parent_id=0){
		$this->db->where($this->dx('parent_id').' = '.$parent_id,NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		return $this->db->count_all_results('menus')>0?TRUE:FALSE;
	}

	function remove_special_url_segments($url = '',$url_segments = array()){
		if(!empty($url_segments)&&$url){
			foreach ($url_segments as $url_segment) {
				$p = strpos($url,$url_segment);
				if ( $p!== false) {
					$url = substr($url,0,$p);
					return $url.'/listing';
				}
			}
			return $url;
		}else{
			return '';
		}
	}

	function display_children($parent_id = 0){
		$links = $this->get_children_links($parent_id);
		if(!empty($links)){
			echo '<ol class="dd-list">';
			foreach($links as $link):
				echo '<li class="dd-item" data-id="'.$link->id.'">
				<div class="dd-handle">
				'.$link->name.' ';
				echo $link->active==1?' - Active':' - Hidden';
				echo '</div>';
				$this->menus_m->display_children($link->id);
				echo '</li>';
			endforeach;
			echo '</ol>';
		}
	}

	function generate_dashboard_menu(){
		$parent_links = $this->get_active_parent_links();
		if($parent_links){
			echo '<div class="tiles">';
			foreach($parent_links as $parent_link){
				if($this->has_active_children($parent_link->id)){
					$children_links = $this->get_active_children_links($parent_link->id);
					echo '
					<div class="portlet light">
					<div class="portlet-title">
					<div class="caption">
					<i class="'.$parent_link->icon.'"></i>'.$parent_link->name.'
					</div>
					<div class="tools">
					<a href="javascript:;" class="expand">
					</a>
					</div>
					</div>
					<div class="portlet-body">';
					foreach($children_links as $child_link){
						echo
						'<a href="'.site_url($child_link->url).'">
						<div class="tile bg-'.$child_link->color.' '.$child_link->size.'">
						<div class="tile-body ">
						<i class="'.$child_link->icon.'"></i>
						</div>
						<div class="tile-object">
						<div class="name">
						'.$child_link->name.'
						</div>
						<div class="number">
						</div>
						</div>
						</div>
						</a>';
					}
					echo '<div class="clearfix"></div></div>
					</div>';
				}
			}
		}
		echo '</div>';
	}

	function get_active_link(){
		$active_url = $this->remove_special_url_segments(uri_string(),$this->special_url_segments);
		$this->select_all_secure('menus');
		$this->db->where($this->dx('url').' = "'.$active_url.'"',NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		return $this->db->get('menus')->row();
	}

	function get_active_children_links_by_url(){
		$parent_menu = $this->get_active_link();
		if($parent_menu){
			return $this->get_active_children_links($parent_menu->id);
		}else{
			return false;
		}
	}

	function generate_dashboard_sub_menu(){
		$parent_link = $this->get_active_link();
		$children_links = $this->get_active_children_links_by_url();
		if($parent_link&&!empty($children_links)){
			$parents = array();
			if($this->all_active_children_have_active_grand_children($parent_link->id)){
				echo '<div class="tiles">';
				foreach($children_links as $child_link):
					if($this->has_active_children($child_link->id)){
						$grand_children_links = $this->get_active_children_links($child_link->id);
						echo '
						<div class="portlet light">
						<div class="portlet-title">
						<div class="caption">
						<i class="'.$child_link->icon.'"></i> '.$parent_link->name.' &raquo; '.$child_link->name.'
						</div>
						<div class="tools">
						<a href="javascript:;" class="expand">
						</a>
						</div>
						</div>
						<div class="portlet-body">';
						foreach($grand_children_links as $grand_child_link):
							echo '<a href="'.site_url($grand_child_link->url).'">
							<div class="tile bg-'.$grand_child_link->color.' '.$grand_child_link->size.'">
							<div class="tile-body ">
							<i class="'.$grand_child_link->icon.'"></i>
							</div>
							<div class="tile-object">
							<div class="name">
							'.$grand_child_link->name.'
							</div>
							<div class="number">
							</div>
							</div>
							</div>
							</a>';
						endforeach;
						echo '<div class="clearfix"></div></div>
						</div>';
					}
				endforeach;
				echo '</div>';
			}else{
				echo '<div class="tiles">';
				echo '
				<div class="portlet light">
				<div class="portlet-title">
				<div class="caption">
				<i class="'.$parent_link->icon.'"></i>'.$parent_link->name.'
				</div>
				<div class="tools">
				<a href="javascript:;" class="expand">
				</a>
				</div>
				</div>
				<div class="portlet-body">';
				foreach($children_links as $child_link):
					if($this->has_active_children($child_link->id)){
										//keep an index of the children
					}else{
						echo '<a href="'.site_url($child_link->url).'">
						<div class="tile bg-'.$child_link->color.' '.$child_link->size.'">
						<div class="tile-body ">
						<i class="'.$child_link->icon.'"></i>
						</div>
						<div class="tile-object">
						<div class="name">
						'.$child_link->name.'
						</div>
						<div class="number">
						</div>
						</div>
						</div>
						</a>';
					}
				endforeach;
				echo '<div class="clearfix"></div></div>
				</div>';

				foreach($children_links as $child_link):
					if($this->has_active_children($child_link->id)){
						$grand_children_links = $this->get_active_children_links($child_link->id);
						echo '
						<div class="portlet light">
						<div class="portlet-title">
						<div class="caption">
						<i class="'.$child_link->icon.'"></i> '.$parent_link->name.' '.$child_link->name.'
						</div>
						<div class="tools">
						<a href="javascript:;" class="expand">
						</a>
						</div>
						</div>
						<div class="portlet-body">';
						foreach($grand_children_links as $grand_child_link):
							echo '<a href="'.site_url($grand_child_link->url).'">
							<div class="tile bg-'.$grand_child_link->color.' '.$grand_child_link->size.'">
							<div class="tile-body ">
							<i class="'.$grand_child_link->icon.'"></i>
							</div>
							<div class="tile-object">
							<div class="name">
							'.$grand_child_link->name.'
							</div>
							<div class="number">
							</div>
							</div>
							</div>
							</a>';
						endforeach;
						echo '<div class="clearfix"></div></div>
						</div>';
					}
				endforeach;
				echo '</div>';
			}
		}
	}

	function all_active_children_have_active_grand_children($parent_id = 0){
		$children_links = $this->get_active_children_links($parent_id);
		if($children_links){
			foreach ($children_links as $child_link) {
				if(!$this->has_active_children($child_link->id)){
					return false;
				}
			}
			return true;
		}else{
			false;
		}
	}

	function get_fellow_active_children_links($parent_id = 0){
		$this->select_all_secure('menus');
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$this->db->where($this->dx('parent_id').' = '.$parent_id,NULL,FALSE);
		$this->db->order_by($this->dx('position').'+0','ASC', FALSE);
		return $this->db->get('menus')->result();
	}

	function generate_header_menus(){
		$current_menu = $this->get_active_link();
		//find fellow children
		if($current_menu){
			$children_links  = $this->get_fellow_active_children_links($current_menu->parent_id);
			if($children_links){

				echo '<div class="actions">';
				foreach($children_links as $child_link){
					if($current_menu->id!==$child_link->id){
						echo '
						<a href="'.site_url($child_link->url).'" class="btn '.$child_link->color.' btn-sm">
						<i class="'.$child_link->icon.'"></i> '.$child_link->name.'
						</a>';
					}else{
						echo '
						<a href="'.site_url($child_link->url).'" class="btn green-meadow btn-sm">
						<i class="'.$child_link->icon.'"></i> '.$child_link->name.'
						</a>';
					}
				}
				echo '</div>';
			}
		}
	}

	function generate_module_dashboard_link(){
		$current_menu = $this->get_active_link();
		if($current_menu){
			$parent_link = $this->get_active_parent_link($current_menu->parent_id);
			if($parent_link){
				echo '
				<a href="'.site_url($parent_link->url).'" class="btn btn-sm blue">
				<i class="'.$parent_link->icon.'"></i> '.$parent_link->name.' Dashboard </a>';
			}else{
				echo '
				<a href="'.site_url('admin').'" class="btn btn-sm blue">
				<i class="icon-home"></i> Dashboard </a>';
			}
		}
	}

	function child_is_active($parent_id,$child_link_url){
		$child_link_url = $this->remove_special_url_segments($child_link_url,$this->special_url_segments);
		if($child_link_url){
			if(isset($this->active_parent_id_as_key_link_as_value_multidimensional_array[$parent_id])){
				$arr = $this->active_parent_id_as_key_link_as_value_multidimensional_array[$parent_id];
				if(in_array($child_link_url,$arr)){
					return TRUE;
				}else{
					return FALSE;
				}
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}

	function grand_child_is_active($parent_id,$grand_child_link_url){
		$grand_child_link_url = $this->remove_special_url_segments($grand_child_link_url,$this->special_url_segments);
		if($grand_child_link_url){
			if(isset($this->active_link_as_key_parent_id_as_value_array[$grand_child_link_url])){
				$menu_child_id = $this->active_link_as_key_parent_id_as_value_array[$grand_child_link_url];
				if(isset($this->active_child_id_as_key_parent_id_as_value_array[$menu_child_id])){
					$menu_id = $this->active_child_id_as_key_parent_id_as_value_array[$menu_child_id];
					if($menu_id == $parent_id){
						return TRUE;
					}else{
						return FALSE;
					}
				}else{
					return FALSE;
				}
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}

	function great_grand_child_is_active($parent_id,$great_grand_child_link_url){
		$great_grand_child_link_url = $this->remove_special_url_segments($great_grand_child_link_url,$this->special_url_segments);
		if($great_grand_child_link_url){
			if(isset($this->active_link_as_key_parent_id_as_value_array[$great_grand_child_link_url])){
				$menu_grand_child_id = $this->active_link_as_key_parent_id_as_value_array[$great_grand_child_link_url];
				if(isset($this->active_child_id_as_key_parent_id_as_value_array[$menu_grand_child_id])){
					$menu_child_id = $this->active_child_id_as_key_parent_id_as_value_array[$menu_grand_child_id];
					if(isset($this->active_child_id_as_key_parent_id_as_value_array[$menu_child_id])){
						$menu_id = $this->active_child_id_as_key_parent_id_as_value_array[$menu_child_id];
						if($menu_id == $parent_id){
							return TRUE;
						}else{
							return FALSE;
						}
					}else{
						return FALSE;
					}
				}else{
					return FALSE;
				}
			}else{
				return FALSE;
			}
		}else{
			return FALSE;
		}
	}

	function has_active_parent($menu_id=0){
		$menu = $this->get($menu_id);
		if($menu->parent_id){
			return $menu->parent_id;
		}else{
			return FALSE;
		}
	}

	function generate_side_bar_menu(){
		$parent_links = $this->get_active_parent_links();
		//print_r($parent_links);
		//die;
		if($parent_links){
			echo '
			<div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
			<div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1" data-ktmenu-dropdown-timeout="500">
			
			<ul class="kt-menu__nav ">
			';
			foreach($parent_links as $link){

				$href = $link->url?site_url($link->url):'javascript:;';
				echo'
				<li class="kt-menu__item ';

				if(uri_string()==$link->url&&$link->url!==''||$this->child_is_active($link->id,uri_string())||$this->grand_child_is_active($link->id,uri_string())||$this->great_grand_child_is_active($link->id,uri_string())){
					echo ' kt-menu__item--active kt-menu__item--open ';
				}

				if($this->has_children($link->id)){
					echo ' kt-menu__item--submenu ';
				}

				echo ' " ';

				if($this->has_children($link->id)){
					echo ' aria-haspopup="true" data-ktmenu-submenu-toggle="hover" ';
					$href = "javascript:;";
				}else{
					echo ' aria-haspopup="true" ';
				}

				echo ' >
				<a  href="'.$href.'" class="kt-menu__link ';

				if($this->has_children($link->id)){
					echo '  kt-menu__toggle ';
				}

				echo '
				">
				<span class="m-menu__item-here"></span>
				<i class="kt-menu__link-icon '.$link->icon.'"></i>
				<span class="kt-menu__link-text">
				'.$link->name.'
				</span>';

				if($this->has_children($link->id)){
					echo '<i class="kt-menu__ver-arrow la la-angle-right"></i>';
				}

				echo '
				</a>';
				$this->generate_side_bar_sub_menu($link->id,array(),FALSE,$link->name);
				echo '
				</li>
				';
				
			}
			echo '
			</ul>
			</div>
			</div>
			';
		}
	}


	function generate_manager_side_bar_menu(){
		$parent_links = $this->get_active_parent_links();
	//print_r($parent_links);
	//die;
		if($parent_links){
			echo '
			<div class="kt-aside-menu-wrapper kt-grid__item kt-grid__item--fluid" id="kt_aside_menu_wrapper">
			<div id="kt_aside_menu" class="kt-aside-menu " data-ktmenu-vertical="1" data-ktmenu-scroll="1" data-ktmenu-dropdown-timeout="500">
			
			<ul class="kt-menu__nav ">
			';
			foreach($parent_links as $link){

				$href = $link->url?site_url($link->url):'javascript:;';
				echo'
				<li class="kt-menu__item ';

				if(uri_string()==$link->url&&$link->url!==''||$this->child_is_active($link->id,uri_string())||$this->grand_child_is_active($link->id,uri_string())||$this->great_grand_child_is_active($link->id,uri_string())){
					echo ' kt-menu__item--active kt-menu__item--open ';
				}

				if($this->has_children($link->id)){
					echo ' kt-menu__item--submenu ';
				}

				echo ' " ';

				if($this->has_children($link->id)){
					echo ' aria-haspopup="true" data-ktmenu-submenu-toggle="hover" ';
					$href = "javascript:;";
				}else{
					echo ' aria-haspopup="true" ';
				}

				echo ' >
				<a  href="'.$href.'" class="kt-menu__link ';

				if($this->has_children($link->id)){
					echo '  kt-menu__toggle ';
				}

				echo '
				">
				<span class="m-menu__item-here"></span>
				<i class="kt-menu__link-icon '.$link->icon.'"></i>
				<span class="kt-menu__link-text">
				'.$link->name.'
				</span>';

				if($this->has_children($link->id)){
					echo '<i class="kt-menu__ver-arrow la la-angle-right"></i>';
				}

				echo '
				</a>';
				$this->generate_side_bar_sub_menu($link->id,array(),FALSE,$link->name);
				echo '
				</li>
				';
				
			}
			echo '
			</ul>
			</div>
			</div>
			';
		}
	}

	function generate_side_bar_sub_menu($parent_id = 0,$acceptable_children=array(),$check_children=FALSE,$parent_name = ""){
		$children_links = $this->get_active_children_links($parent_id);
		if($children_links){
			echo '
			<div class="kt-menu__submenu ">
			<span class="kt-menu__arrow"></span>
			<ul class="kt-menu__subnav">
			<li class="kt-menu__item  kt-menu__item--parent" aria-haspopup="true"><span class="kt-menu__link"><span class="kt-menu__link-text">'.$parent_name.'</span></span></li>
			';
			foreach($children_links as $child_link){
				$href = $child_link->url?site_url($child_link->url):'javascript:;';
				echo '
				<li class="kt-menu__item ';
				if(uri_string()==$child_link->url&&$child_link->url!==''||$this->child_is_active($child_link->id,uri_string())||$this->grand_child_is_active($child_link->id,uri_string())||$this->great_grand_child_is_active($child_link->id,uri_string())){
					echo 'kt-menu__item--active  kt-menu__item--open ';
				}
				if($this->has_children($child_link->id)){
					echo " kt-menu__item--submenu ";
					$href = "javascript:;";
				}

				echo '
				" aria-haspopup="true"  data-redirect="true" aria-haspopup="true" data-ktmenu-submenu-toggle="hover">

				<a href="'.$href.'" class="kt-menu__link ';

				if($this->has_children($child_link->id)){
					echo ' kt-menu__toggle ';
				}

				echo '">

				<i class="kt-menu__link-icon '.$child_link->icon.'"><span></span></i>

				<span class="kt-menu__link-text">'.$child_link->name.'</span>';

				if($this->has_children($child_link->id)){
					echo '<i class="kt-menu__ver-arrow la la-angle-right"></i>';
				}
				
				echo '
				</a>';
				$this->generate_side_bar_sub_menu($child_link->id,array(),FALSE,$child_link->name);
				echo '
				</li>';
			}
			echo '
			</ul>
			</div>
			';
		}
	}

	function get_menu_by_link_url($link_url=''){
		$this->select_all_secure('menus');
		$this->db->where($this->dx('url').' = "'.$link_url.'"',NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$this->db->order_by($this->dx('position').'+0','ASC', FALSE);
		return $menu = $this->db->get('menus')->row();
	}

	function get_current_url_id($link_url=''){
		$this->db->select(array('id as id'));
		$this->db->where($this->dx('url').' = "'.$link_url.'"',NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$menu = $this->db->get('menus')->row();
		if($menu){
			return $menu->id;
		}else{
			return FALSE;
		}
	}

	function get_dashboard_id(){
		$this->db->select(array('id as id'));
		$this->db->where($this->dx('name').' = "Dashboard"',NULL,FALSE);
		$this->db->where($this->dx('parent_id').'="0"',NULL,FALSE);
		$this->db->where($this->dx('active').' = "1" ',NULL,FALSE);
		$menu = $this->db->get('menus')->row();
		if($menu){
			return $menu->id;
		}else{
			return FALSE;
		}
	}

	function generate_page_quick_action_menus(){
		$link_url = uri_string();
		$menu = $this->get_menu_by_link_url($link_url);
		if($menu){
			if($menu->parent_id){
				echo '
				<ul class="m-portlet__nav">
				<li class="m-portlet__nav-item m-dropdown m-dropdown--inline blue m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
				<a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill  btn-secondary m-btn m-btn--label-brand">
				Quick Links
				</a>
				<div class="m-dropdown__wrapper">
				<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 35.5px;"></span>
				<div class="m-dropdown__inner">
				<div class="m-dropdown__body">
				<div class="m-dropdown__content">
				<ul class="m-nav">';
				$parent_id = $menu->parent_id;
				$parent_menu = $this->get($parent_id);
				$children = $this->get_children_links($parent_id);
				if($children){
					foreach ($children as $child) {
						$href = $child->url?site_url($child->url):'javascript:;';
						echo '
						<li class="m-nav__item">
						<a href="'.$href.'" class="m-nav__link">
						<i class="m-nav__link-icon '.$child->icon.'"></i>
						<span class="m-nav__link-text">
						'.$child->name.'
						</span>
						</a>
						</li>';
					}
				}
				echo "</ul>
				</div>
				</div>
				</div>
				</div>
				</li>
				</ul>
				";
			}
		}else{
			$link_url = uri_string();
			foreach ($this->special_url_segments as $key => $value){
				$segment = explode('/', $value);
				if(preg_match('/'.$segment[0].'\/'.$segment[1].'/', $link_url)){
					$link_url = $this->uri->segment(1).'/'.$this->uri->segment(2).'/';
					$menu = $this->get_menu_by_link_url($link_url)?:$this->get_menu_by_link_url($link_url.'listing');
					if($menu){
						if($menu->parent_id){
							echo '
							<ul class="m-portlet__nav">
							<li class="m-portlet__nav-item m-dropdown m-dropdown--inline blue m-dropdown--arrow m-dropdown--align-right m-dropdown--align-push" data-dropdown-toggle="hover" aria-expanded="true">
							<a href="#" class="m-portlet__nav-link m-dropdown__toggle dropdown-toggle btn btn--sm m-btn--pill  btn-secondary m-btn m-btn--label-brand">
							Quick Links
							</a>
							<div class="m-dropdown__wrapper">
							<span class="m-dropdown__arrow m-dropdown__arrow--right m-dropdown__arrow--adjust" style="left: auto; right: 35.5px;"></span>
							<div class="m-dropdown__inner">
							<div class="m-dropdown__body">
							<div class="m-dropdown__content">
							<ul class="m-nav">';
							$parent_id = $menu->parent_id;
							$parent_menu = $this->get($parent_id);
							$children = $this->get_children_links($parent_id);
							if($children){
								foreach ($children as $child) {
									$href = $child->url?site_url($child->url):'javascript:;';
									echo '
									<li class="m-nav__item">
									<a href="'.$href.'" class="m-nav__link">
									<i class="m-nav__link-icon '.$child->icon.'"></i>
									<span class="m-nav__link-text">
									'.$child->name.'
									</span>
									</a>
									</li>';
								}
							}
							echo "</ul>
							</div>
							</div>
							</div>
							</div>
							</li>
							</ul>
							";
							break;
						}
					}
				}
			}
		}
	}

	function get_menu_options(){
		$parent_links = $this->get_active_parent_links();
		$child_menu = array();
		$parent_name = array();
		if($parent_links){
			foreach ($parent_links as $parent){
				$parent_name[$parent->id] = $parent->name;
			}
		}
		foreach ($parent_name as $key=>$parent) {
			if($this->has_active_children($key)){
				$children_links = $this->get_active_children_links($key);
				$i=0;
				foreach ($children_links as $child) {
					if($this->has_active_children($child->id)){
						$grand_children_links = $this->get_active_children_links($child->id);
						foreach ($grand_children_links as $grand_child) {
							$child_menu[$parent][$child->id] = $child->name;
							$child_menu[$child->name][$grand_child->id] = $grand_child->name;
						}
					}else{
						$child_menu[$key] = $parent;
						$child_menu[$parent][$child->id] = $child->name;
					}
				}
			}else{
				$child_menu[$key] = $parent;
			}
		}
		return $child_menu;
	}
}
