<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Moduleoperations extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    // Load language
    $this->lang->load("setting","english");
    // Load Form and Form Validation
    $this->load->helper('form');
    $this->load->library('form_validation');
    // Check the user is loggedin or not
    $this->is_logged_in();
  }

  //To be check the acl condition and to be allow load form 
  public function add()
  {
    //Acl Permission To Load Form
    if( $this->acl_permits('setting.module_operations_add') )
    {
      $this->loadForm();
    }
    else
    {
      //Unauthorized User Message
      $viewData = '';
      $data     = array(
                        'title'     =>  $this->lang->line('unauth_page_title'),
                        'content'   =>  $this->load->view('unauthorized',$viewData,TRUE)
                       );
      $this->load->view('base/error_template', $data);        
    }  
  }

  //To be load the form with array values
  public function loadForm($formData = array())
  {
    $formData['ActionUrl']      = 'modules/Moduleoperations/formSubmit';  
    $formData['moduleDropdown'] = $this->mcommon->Dropdown('acl_categories', array('category_id as Key', 'category_code as Value'));

    $viewData = array(
                        'form_title'        => $this->lang->line('moduleoperations_form_title'),
                        'list_title'        => $this->lang->line('moduleoperations_list_title'),
                        'form_view'         => $this->load->view('modules/moduleoperationform', $formData, TRUE)
                      );

    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_module_name'), lang('label_operation_name'), lang('label_action_description'), lang('label_action'));

    $viewData['dataTableUrl']  = 'modules/Moduleoperations/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('moduleoperations_form_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData,TRUE)
                  );

    $this->load->view($this->dbvars->app_template, $data);
  }

  //To be Submit the form with POST values. The form either Inset or Edit with the codtion
  public function formSubmit($viewData='')
  {
    if(!empty($_POST))
    {
      $this->form_validation->set_rules('category_id', $this->lang->line('label_module'), 'required');
      $this->form_validation->set_rules('action_code', $this->lang->line('label_operation_name'), 'required|trim');
      $this->form_validation->set_rules('action_desc', $this->lang->line('label_action_description'), 'required|trim');

      if($this->form_validation->run() == TRUE)
      {
        $action_code  =  str_replace(' ', '_', strtolower($this->input->post('action_code')));

        if($this->input->post('action_id') == "")
        {
          $data       = array(
                              'action_id'       => $this->input->post('action_id'),
                              'action_code'     => $action_code,
                              'action_desc'     => $this->input->post('action_desc'),
                              'category_id'     => $this->input->post('category_id')
                              );
          $result     = $this->mcommon->common_insert('acl_actions', $data);

          if($result)
          {
            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('modules/Moduleoperations/add'));
          }
        }
        else
        {
          $data        = array(
                              'action_code'     => $action_code,
                              'action_desc'     => $this->input->post('action_desc'),
                              'category_id'     => $this->input->post('category_id')
                             );

          $where_array = array('action_id' => $this->input->post('action_id'));
          $result      = $this->mcommon->common_edit('acl_actions', $data, $where_array);

          if($result)
          {
              $this->session->set_flashdata('msg', 'Updated Successfully');
              $this->session->set_flashdata('alertType', 'success');
              redirect(base_url('modules/Moduleoperations/add'));
          }
          else
          {
            $this->session->set_flashdata('msg', 'No changes has been done');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('modules/Moduleoperations/add'));
          }
        }
      }
    }
    $this->loadForm();
  }

  //To be Edit the Data
  public function edit($action_id='')
  {
    //Acl Permission For Edit
    if( $this->acl_permits('setting.module_operations_edit') )
    {
        $constraint_array           = array('action_id' => $action_id);
        $formData['tabledata']      = $this->mcommon->records_all('acl_actions', $constraint_array);
        $this->loadForm($formData);
    }
    else
    {
      //Unauthorized User Message
      $viewData = '';
      $data     = array(
                        'title'     =>  $this->lang->line('unauth_page_title'),
                        'content'   =>  $this->load->view('unauthorized',$viewData,TRUE)
                       );
      $this->load->view('base/error_template', $data);        
    } 
  }

  //To be Delete the Data
  public function delete($action_id='')
  {
    //Acl Permission For Delete
    if( $this->acl_permits('setting.module_operations_delete') )
    {
      $where_array = array('action_id' => $action_id);
      $result      = $this->mcommon->common_delete('acl_actions',$where_array);

      if($result)
      {
        $this->session->set_flashdata('msg', 'Deleted Successfully');
        $this->session->set_flashdata('alertType', 'success');
        redirect(base_url('setting/modules/Moduleoperations/add'));
      }
    }
    else
    {
      //Unauthorized User Message
      $viewData = '';
      $data     = array(
                        'title'     =>  $this->lang->line('unauth_page_title'),
                        'content'   =>  $this->load->view('unauthorized', $viewData, TRUE)
                       );
      $this->load->view('base/error_template', $data);        
    }
  }

  //Take records and view to datatable
  public function datatable()
  {
    $this->datatables ->select('c.category_code, a.action_code, a.action_desc, a.action_id')
                      ->from('acl_actions as a')
                      ->join('acl_categories as c', 'a.category_id = c.category_id');
    $this->datatables ->edit_column('a.action_id', '$1', 'get_buttons(a.action_id, "modules/Moduleoperations/")');
    echo $this->datatables->generate();
  }
}