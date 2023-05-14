<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/*** @package   Kice-Foundation
 * @subpackage  Categories
 * @category    Module
 */
class Admin extends Admin_Controller{
/* * The id of post
* @access protected
* @var int
*/
    protected $data = array();
    
    protected $validation_rules = array(
        array(
            'field' => 'title',
            'label' => 'Email Template Title',
            'rules' => 'trim|required',
        ),
        array(
            'field' => 'description',
            'label' => 'Email Template Description',
            'rules' => 'trim',
        ),
        array(
            'field' => 'content',
            'label' => 'Template Content',
            'rules' => 'trim|required',
        ),
    );

    public function __construct(){
        parent::__construct();
        $this->load->model('email_templates_m');
    }

    /*
     * Show all created posts
     * @access public
     * @return void
     */
    public function index(){
        $this->template->title('Email Templates Dashboard')->build('admin/index', $this->data);
    }

    public function listing(){
        $total_rows = $this->email_templates_m->count_all();
        $pagination = create_pagination('admin/email_templates/listing', $total_rows,250);

        $posts = $this->email_templates_m->limit($pagination['limit'])->get_all();
        $this->data['posts'] = $posts;
        $this->data['pagination'] = $pagination;
        $this->template->title('List Email Templates')->build('admin/listing', $this->data);
    }

    function _is_unique_template(){
        $slug = $this->input->post('slug');
        $id = $this->input->post('id');
        if(empty($slug)){
            $this->form_validation->set_message('_is_unique_template','Email Template slug is required');
            return FALSE;
        }else{
            $res = $this->email_templates_m->get_by_slug($slug,$id);
            if($res){
                $this->form_validation->set_message('_is_unique_template','Email Template already exists');
                return FALSE;
            }else{
                return TRUE;
            }
        }
    }

    public function create(){
        $post = new stdClass();
        $this->form_validation->set_rules($this->validation_rules);
        if($this->form_validation->run()){
            $id = $this->email_templates_m->insert(
                array(
                    'title' => $this->input->post('title'),
                    'slug' => generate_slug($this->input->post('title')),
                    'description' => $this->input->post('description'),
                    'content' => $this->input->post('content'),
                    'created_on' => time(),
                    'created_by' => $this->ion_auth->get_user()->id,
                )
            );
            if($id){
                $this->session->set_flashdata('success',$this->input->post('title').' successfully created');
                if($this->input->post('new_item')){
                    redirect('admin/email_templates/create','refresh');
                }else{
                    redirect('admin/email_templates/edit/'.$id,'refresh');
                }
            }else{
                $this->session->set_flashdata('error', 'Error adding Email Templates');
                redirect('admin/email_templates/listing','refresh');
            }
        }else{
            // Go through all the known fields and get the post values
            foreach ($this->validation_rules as $key => $field){
                $field_name = $field['field'];
                $post->$field_name = set_value($field['field']);
            }
        }
        $this->data['post'] = $post;
        $this->data['id'] = '';
        $this->template->title('Create Email Template')->build('admin/form', $this->data);
    }



    public function edit($id=0)
    {
        $id OR redirect('admin/email_templates');
        $post = new stdClass();

        $post = $this->email_templates_m->get($id);
        if(empty($post))
        {
            $this->session->set_flashdata('error','Sorry the template does not exist');
            redirect('admin/email_templates/listing','refresh');
        }

        $this->form_validation->set_rules($this->validation_rules);

        //initialize the filenames with the filenames stored

        if ($this->form_validation->run())
        {
            $result = $this->email_templates_m->update($id,
                array(
                    'title'         => $this->input->post('title'),
                    'slug'          =>generate_slug($this->input->post('title')),
                    'description'   => $this->input->post('description'),
                    'content'       => $this->input->post('content'),
                    'modified_on'   => time(),
                    'modified_by'   => $this->ion_auth->get_user()->id,
            ));

            if ($result)
            {
                $this->session->set_flashdata('success' ,$this->input->post('title').' successfully updated');
                if($this->input->post('new_item'))
                {
                    redirect('admin/email_templates/create');
                }
                else
                {
                    redirect('admin/email_templates/listing');
                }
            }

            else
            {
                $this->session->set_flashdata('error','Error  Editing '.$post->title.' Email Templates');
            }

            // Redirect back to the form or main page
            redirect('admin/email_templates/listing');
        }



        // Go through all the known fields and get the post values
        foreach (array_keys($this->validation_rules) as $field)
        {
             if (isset($_POST[$field]))
            {
                $post->$field = $this->form_validation->$field;
            }
        }

        $this->data['post'] = $post;
        $this->data['id'] = $id;
        // Load WYSIWYG editor
        $this->template->title('Edit Template')->build('admin/form', $this->data);

    }


    public function action()
    {
        switch ($this->input->post('btnAction'))
        {
            case 'publish':
                $this->publish();
                break;
            case 'bulk_delete':
                $this->delete();
                break;
            default:
                redirect('admin/email_templates/listing');
                break;
        }
    }


    public function delete($id = 0)
    {
        // Delete one
        $ids = ($id) ? array($id) : $this->input->post('action_to');
        // Go through the array of slugs to delete
        if (!empty($ids))
        {
            $post_titles = array();
            foreach ($ids as $id)
            {
                // Get the current page so we can grab the id too
                if ($post = $this->email_templates_m->get($id))
                {
                    $this->email_templates_m->delete($id);
                    // Wipe cache for this model, the content has changed
                    $post_titles[] = $post->title;
                }
            }
        }
        // Some pages have been deleted

        if (!empty($post_titles))
        {
            // Only deleting one page
            if (count($post_titles) == 1)
            {
                $this->session->set_flashdata('success', sprintf(' Email Templates Deleted', $post_titles[0]));
            }
            // Deleting multiple pages
            else
            {
                $this->session->set_flashdata('success', sprintf(' Email Templates Deleted', implode('", "', $post_titles)));
            }
        }
        // For some reason, none of them were deleted
        else
        {
            $this->session->set_flashdata('info', 'Items: Email Templates Deleted');
        }
        redirect('admin/email_templates/listing');
    }


}
