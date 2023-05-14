<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{

    protected $rules = array(
        array(
            'field' =>  'name',
            'label' =>  'Menu Name',
            'rules' =>  'trim|required',
        ),
        array(
            'field' =>  'url',
            'label' =>  'Menu URL',
            'rules' =>  'trim|required',
        ),
        array(
            'field' =>  'icon',
            'label' =>  'Menu Icon',
            'rules' =>  'trim|required',
        ),
        array(
            'field' =>  'parent_id',
            'label' =>  'Parent Menu',
            'rules' =>  'trim',
        ),
    );

    protected $data = array();

	function __construct(){
        parent::__construct();
        $this->load->model('manager_menus_m');
    }

    function delete($id = 0,$redirect= TRUE){
        $id OR redirect('admin/manager_menus/listing');
        $post = $this->manager_menus_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the Admin Menu does not exist');
            redirect('admin/manager_menus/listing');
        }
        $id = $this->manager_menus_m->delete($post->id);
        if($id){
            $this->session->set_flashdata('success',$post->name.' was successfully deleted');
        }else{
            $this->session->set_flashdata('error','Unable to delete '.$post->name.' Admin menu');
        }
        if($redirect){
            redirect('admin/manager_menus/listing');
        }
        return TRUE;
    }

    function hide($id=0,$redirect=TRUE){
        $id OR redirect('admin/manager_menus/listing');
        $post = $this->manager_menus_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the Admin Menu does not exist');
            redirect('admin/manager_menus/listing');
        }
        if($post->active == ''){
            $this->session->set_flashdata('error','Sorry, the Admin Menu is already hidden');
            redirect('admin/manager_menus/listing');
        }
        $id = $this->manager_menus_m->update($post->id,array
            (
                'active' => 0,
                'modified_on' => time(),
                'modified_by' => $this->user->id,
            )
        );
        if($id){
            $this->session->set_flashdata('success',$post->name.' was successfully hidden');
        }else{
            $this->session->set_flashdata('error','Unable to hide '.$post->name.' Admin menu');
        }
        if($redirect){
            redirect('admin/manager_menus/listing');
        }
        return TRUE;
    }

    function activate($id = 0,$redirect = TRUE){
        $id OR redirect('admin/manager_menus/listing');
        $post = $this->manager_menus_m->get($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry, the Admin Menu does not exist');
            redirect('admin/manager_menus/listing');
        }
        if($post->active)
        {
            $this->session->set_flashdata('error','Sorry, the Admin Menu is already activated');
            redirect('admin/manager_menus/listing');
        }
        $id = $this->manager_menus_m->update($post->id,array
            (
                'active' => 1,
                'modified_on' => time(),
                'modified_by' => $this->ion_auth->get_user()->id,
            )
        );
        if($id){
            $this->session->set_flashdata('success',$post->name.' was successfully activated');
        }else{
            $this->session->set_flashdata('error','Unable to hide '.$post->name.' Admin menu');
        }
        if($redirect){
            redirect('admin/manager_menus/listing');
        }
        return TRUE;
    }

    function listing(){
        $this->data['posts'] = $this->manager_menus_m->get_parent_links();
        $this->data['side_bar_menu_options'] = $this->manager_menus_m->get_options();
        $this->template->title('Menu Listing')->build('admin/listing',$this->data);
    }

    function create(){
        $post = new StdClass();
        $menu = new StdClass();
        $this->form_validation->set_rules($this->rules);
        if($this->form_validation->run()){
             $data = array(
                'parent_id' => $this->input->post('parent_id')?$this->input->post('parent_id'):0,
                'name' => $this->input->post('name'),
                'url' => $this->input->post('url'),
                'icon' => $this->input->post('icon'),
                'created_by' => $this->user->id,
                'created_on' => time(),
                'active' => 1,
            );
            $id = $this->manager_menus_m->insert($data);
            if($id){
                $this->session->set_flashdata('success',' Menu Item Created Successfully.');
                if($this->input->post('new_item')){
                    redirect('admin/manager_menus/create','refresh');
                }else{
                    redirect('admin/manager_menus/edit/'.$id);
                }
            }else{
                $this->session->set_flashdata('error','Menu Item could not be Created.');
                redirect('admin/manager_menus/create');
            }
        }else{
            foreach ($this->rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
                $menu->$field_name = set_value($field['field']);
            }
        }
        $this->data['menus'] = $this->manager_menus_m->get_options();
        $this->data['menu'] = $menu;
        $this->data['post'] = $post;
        $this->template->title('Create Menu')->build('admin/form',$this->data);
    }

    function action(){
        $action_to = $this->input->post('action_to');
        $action = $this->input->post('btnAction');
        if($action == 'bulk_delete'){
            for($i=0;$i<count($action_to);$i++){
                $this->delete($action_to[$i],FALSE);
            }
        }
        redirect('admin/manager_menus/listing');
    }

    function sort(){
        $this->data['posts'] = $this->manager_menus_m->get_parent_links();
        $this->template->title('Sort User Menus')->build('admin/sort', $this->data);
    }

    function edit($id = 0){
        $id OR redirect('admin/manager_menus/listing');
        $post = new stdClass();
        $post = $this->manager_menus_m->get($id);

        if($post){

        }else{
            $this->session->set_flashdata('error','Sorry, the  Menu does not exist');
            redirect('admin/manager_menus/listing');
        }
        $this->form_validation->set_rules($this->rules);
        if($this->form_validation->run()){
             $data = array( 
                'parent_id' => $this->input->post('parent_id')?$this->input->post('parent_id'):0,
                'name' => $this->input->post('name'),
                'url' => $this->input->post('url'),
                'icon' => $this->input->post('icon'),
                'modified_by' => $this->user->id,
                'modified_on' => time(),
            );
            $update = $this->manager_menus_m->update($id,$data);
            if($update){
                $this->session->set_flashdata('success',$this->input->post('name').' successfully updated');
                if($this->input->post('new_item')){
                    redirect('admin/manager_menus/create','refresh');
                }else{
                    redirect('admin/manager_menus/listing','refresh');
                }
            }else{
                $this->session->set_flashdata('error','Unable to update');
                redirect('admin/manager_menus/listing','refresh');
            }
        }else{
            foreach ($this->rules as $key => $field) {
                $field_name = $field['field'];
                if(set_value($field['field'])){
                    $post->$field_name = set_value($field['field']);
                }
            }
        }
        $this->data['menus'] = $this->manager_menus_m->get_options();
        $this->data['post'] = $post;
        $this->template->title('Edit Menu')->build('admin/form',$this->data);
    }


    function ajax_sort_update()
    {
        $data = json_decode($this->input->post('json'));
        for($i=0;$i<count($data);$i++){
            $this->manager_menus_m->update($data[$i]->id,array(
                'position'=>$i,
                'modified_on' => time(),
                'modified_by' => $this->ion_auth->get_user()->id,
            ));
            $this->_children($data[$i],0,$i);
        }
    }

    private function _children($pt,$parent_id,$position){
        echo "Dashboard I:".$pt->id."P:".$parent_id."||";
        $this->manager_menus_m->update($pt->id,array(
            'position'=>$position,
            'parent_id'=>$parent_id,
            'modified_on' => time(),
            'modified_by' => $this->ion_auth->get_user()->id,
        ));
        $k=0;
        if(isset($pt->children)){
            foreach($pt->children as $child){
                $k++;
                $this->_children($child,$pt->id,$k);
            }
        }

    }

}
