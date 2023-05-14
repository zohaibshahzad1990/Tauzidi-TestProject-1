<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends Admin_Controller{

  protected $validation_rules=array(
        
        array(
            'field'     =>  'protocol',
            'label'     =>  '<?php echo $post->application_name; ?> Protocol',
            'rules'     =>  'trim',
         ),
        array(
                'field'     =>  'disable_smses',
                'label'     =>  'disable smses',
                'rules'     =>  'trim',
            ),
         array(
                'field'     =>  'enable_sms_delivery',
                'label'     =>  'enable sms delivery',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'url',
                'label'     =>  '<?php echo $post->application_name; ?> URL',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'favicon',
                'label'     =>  '<?php echo $post->application_name; ?> Favicon',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'logo',
                'label'     =>  '<?php echo $post->application_name; ?> Logo',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'paper_header_logo',
                'label'     =>  '<?php echo $post->application_name; ?> Paper Header Logo',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'paper_footer_logo',
                'label'     =>  '<?php echo $post->application_name; ?> Paper Footer Logo',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'admin_login_logo',
                'label'     =>  'Admin Login Page Logo',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'responsive_logo',
                'label'     =>  'Responsive Logo',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'application_name',
                'label'     =>  'Application Name',
                'rules'     =>  'trim|required',
            ),
        array(
                'field'     =>  'sender_id',
                'label'     =>  'Sender ID',
                'rules'     =>  'trim',
            ),
        array(
                'field'     =>  'home_page_controller',
                'label'     =>  'Home Page Controller',
                'rules'     =>  'trim',
            )
        
    );

    protected $data = array();

    protected $path = 'uploads/logos';

  function __construct(){
        parent::__construct();
        $this->load->library('files_uploader');
        $this->load->model('settings_m');
    }

    function index(){
        redirect('admin/settings/view');
    }

    public function create(){
        $post = new stdClass();      
        
        $this->form_validation->set_rules($this->validation_rules);
        
        if($this->settings_m->get_all()){
            redirect('admin/settings/edit/1');
        }

        if($this->form_validation->run()){
            $logo_directory = '../uploads/logos';
            if(!is_dir($logo_directory)){
                mkdir($logo_directory,0777,TRUE);
            }
            $favicon = $this->files_uploader->upload('favicon',$this->path);
            $logo = $this->files_uploader->upload('logo',$this->path);
            // $paper_header_logo = $this->files_uploader->upload('paper_header_logo',$this->path);
            // $paper_footer_logo = $this->files_uploader->upload('paper_footer_logo',$this->path);
            // $admin_login_logo = $this->files_uploader->upload('admin_login_logo',$this->path);
            // $responsive_logo  = $this->files_uploader->upload('responsive_logo',$this->path);
            $disable_smses_value = $this->input->post('disable_smses');
            $sms_delivery_value = $this->input->post('enable_sms_delivery');
            if($disable_smses_value == 1){
                //do nothing
            }else{
                $disable_smses_value = 0;
            }
            if($sms_delivery_value == 1){
                //do nothing
            }else{
                $sms_delivery_value = 0;
            }
            $input = array(
                'application_name'  =>  $this->input->post('application_name'),
                'protocol'  =>  $this->input->post('protocol'),
                'url'  =>  $this->input->post('url'),
                'favicon'       =>  $favicon['file_name']?:'',
                'logo'          =>  $logo['file_name']?:'',
                // 'admin_login_logo'=> $admin_login_logo['file_name']?:'',
                // 'sms_delivery_enabled'         =>  $sms_delivery_value,
                // 'disable_smses'         =>  $disable_smses_value,
                // 'paper_header_logo'=>$paper_header_logo['file_name']?:'',
                // 'paper_footer_logo'=>$paper_footer_logo['file_name']?:'',
                // 'responsive_logo'  =>$responsive_logo['file_name']?:'',
                'created_by'    =>  $this->ion_auth->get_user()->id,
                'created_on'    =>  time(),
                'active'        =>  1,
            );

            $id = $this->settings_m->insert($input);
            if($id){
                $this->session->set_flashdata('success','Settings successfully added');
                redirect('admin/settings/edit/'.$id);
            }else{
                $this->session->set_flashdata('error','Unable to add the setting. Please try again');
                redirect('admin/settings/create');
            }
           
        }else{
            foreach ($this->validation_rules as $key => $field) {
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
        }
        $this->data['id'] = '';
        $this->data['post'] = $post;
        $this->template->title('Create Settings')->build('admin/form',$this->data);
    }

    function edit($id=0){   
      
        $post = $this->settings_m->get_settings($id);
        if(!$post){
            $this->session->set_flashdata('error','Sorry the setting does not exist');
            redirect('admin/settings/create');
        }
        $this->form_validation->set_rules($this->validation_rules);
        $favicon['file_name'] = '';
        $logo['file_name'] = ''; 
        // $paper_header_logo['file_name']='';
        // $paper_footer_logo['file_name']='';
        // $admin_login_logo['file_name']='';
        // $group_login_logo['file_name']='';
        // $responsive_logo['file_name']='';
        if($this->form_validation->run()){
            $logo_directory = '../uploads/logos';
            if(!is_dir($logo_directory)){
                mkdir($logo_directory,0777,TRUE);
            }
            if($_FILES['favicon']['name']){

                $favicon = $this->files_uploader->upload('favicon',$this->path);
                 if($favicon)
                 {
                    if(is_file(FCPATH.$this->path.'/'.$post->favicon)){
                        if(unlink(FCPATH.$this->path.'/'.$post->favicon)){
                            $this->session->set_flashdata('info','Favicon Icon successfully replaced');
                        }
                    }

                 }
            }

            if($_FILES['logo']['name'])
            {
                 $logo = $this->files_uploader->upload('logo',$this->path);

                 if($logo)
                 {
                    if(is_file(FCPATH.$this->path.'/'.$post->logo)){
                        if(unlink(FCPATH.$this->path.'/'.$post->logo)){
                            $this->session->set_flashdata('info','<?php echo $post->application_name; ?> Logo successfully replaced');
                        }
                    }

                 }
            }

            
            $disable_smses_value = $this->input->post('disable_smses');
            $sms_delivery_value = $this->input->post('enable_sms_delivery');
            if($disable_smses_value == 1){
                //do nothing
            }else{
                $disable_smses_value = 0;
            }
            if($sms_delivery_value == 1){
                //do nothing
            }else{
                $sms_delivery_value = 0;
            }
            $data = array(
                'application_name'         =>  $this->input->post('application_name'),
                'sender_id'         =>  $this->input->post('sender_id'),
                'url'                   =>  $this->input->post('url'),
                'protocol'              =>  $this->input->post('protocol'),
                // 'sms_delivery_enabled'         =>  $sms_delivery_value,
                // 'disable_smses'         =>  $disable_smses_value,
                'favicon'               =>  $favicon['file_name']?:$post->favicon,
                'logo'                  =>  $logo['file_name']?:$post->logo,
                // 'paper_footer_logo'     =>  $paper_footer_logo['file_name']?:$post->paper_footer_logo,
                // 'paper_header_logo'     =>  $paper_header_logo['file_name']?:$post->paper_header_logo,
                // 'admin_login_logo'      =>  $admin_login_logo['file_name']?:$post->admin_login_logo,
                // 'responsive_logo'       =>  $responsive_logo['file_name']?:$post->responsive_logo,
                'modified_by'           =>  $this->ion_auth->get_user()->id,
                'modified_on'           =>  time(),
            );

            $update = $this->settings_m->update($post->id,$data);

            if($update)
            {  
                $this->session->set_flashdata('success','<?php echo $post->application_name; ?> Settings successfully updated');
            }
            else
            {
                $this->session->unset('info');
                $this->session->set_flashdata('error','Unable to update the settings');
            }

            redirect('admin/settings/view');
        }else{

        }
        $this->data['path'] = $this->path;
        $this->data['post'] = $post;
        $this->data['id'] = '';
        $this->template->title('Edit <?php echo $post->application_name; ?> Settings')->build('admin/form',$this->data);
    }

    function view()
    {
        $post = $this->settings_m->get_settings(1);
        if(empty($post))
        {
            redirect('admin/settings/create','create');
        }
        $this->data['post'] = $post;
        $this->data['path'] = $this->path;
        $this->template->title('Application Settings')->build('admin/view',$this->data);
    }

}