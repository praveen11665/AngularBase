<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Notification extends MY_Controller 
{
  public function __construct()
  {
    parent::__construct();        
    // Load language
    $this->lang->load("master_lang","english");
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
    if( $this->acl_permits('master.notification_add') )
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
  public function loadForm($formData=array())
  {
    //Page Config
    $formData['ActionUrl']     = 'master/notification/formSubmit';  
    $viewData = array(
                        'form_title'        => $this->lang->line('label_create_notification'),
                        'list_title'        => $this->lang->line('label_notification_list'),
                        'form_view'         => $this->load->view('master/notification_form', $formData, TRUE)
                      );
    //Table Config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_notification_title'), lang('label_notification_content'), lang('label_notification_modules'), lang('label_action'));

    $viewData['dataTableUrl']  = 'master/notification/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('label_notification_form_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData,TRUE)
                  );

    $this->load->view($this->dbvars->app_template, $data);
  }

  //To be Submit the form with POST values. The form either Inset or Edit with the codtion
  public function formSubmit($viewData='')
  {
    if(!empty($_POST))
    {
      //Validation Rules
      $this->form_validation->set_rules('title', $this->lang->line('label_notification_title'), 'required');          
      $this->form_validation->set_rules('content', $this->lang->line('label_notification_content'), 'required');

      //If check Modules already exist in notification table
      if($this->input->post('notify_id') == "")
      {
        $this->form_validation->set_rules('modules_id', $this->lang->line('label_notification_modules'), 'required');
      }
      
      if($this->form_validation->run() == TRUE)
      {
        if($this->input->post('notify_id') == "")
        {
          //Insert
          $data       = array(                                              
                                'title'       => $this->input->post('title'),
                                'content'     => $this->input->post('content'),
                                'modules_id'  => $this->input->post('modules_id'),                               
                                'created_on'  => date('Y-m-d H:i:s'),
                                'created_by'  => $this->auth_user_id,
                                'updated_by'  => $this->auth_user_id,
                              );
          $result     = $this->mcommon->common_insert('notification', $data);

          if($result)
          {
            //Success Message After Insertion
            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/notification/add'));
          }
        }
        else
        {
          //Update
          $data       = array(                                              
                                'title'         => $this->input->post('title'),
                                'content'       => $this->input->post('content'),
                                //'modules_id'    => $this->input->post('modules_id'),
                                'updated_by'    => $this->auth_user_id,
                              );
                     
          $where_array = array('notify_id' => $this->input->post('notify_id'));         
          $result      = $this->mcommon->common_edit('notification', $data, $where_array);
   
          if($result)
          {
            //Success Message After Update
            $this->session->set_flashdata('msg', 'Updated Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/notification/add'));
          }
          else
          {
            //Message while Submitting Form Without Any Update
            $this->session->set_flashdata('msg', 'No Data Has Been Changed');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('master/notification/add'));
          }  
        }
      }
    }
    $this->loadForm();
  }  

  //To be Edit the Data
  public function edit($notify_id='')
  {
    echo $this->mcommon->row_records_all('notification', array('notify_id' => $notify_id));
  }

  //To be Delete the Data
  public function delete($notify_id='')
  {
    //Acl Permission For Delete
    if( $this->acl_permits('master.notification_delete') )
    {
      $where_array = array('notify_id' => $notify_id);
      $result      = $this->mcommon->common_delete('notification',$where_array);
      
      if($result)
      {
        $this->session->set_flashdata('msg', 'Deleted Successfully');
        $this->session->set_flashdata('alertType', 'success');
        redirect(base_url('master/notification/add'));
      }
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
  
  //Take records and view to datatable
  public function datatable()
  {
    $this->datatables->select('n.title, n.content, dm.module_name, n.notify_id')
    ->from('notification as n')
    ->join('def_modules as dm', 'dm.modules_id = n.modules_id', 'left');
    $this->db->order_by('n.updated_on', DESC);                                     
    $this->datatables->edit_column('n.notify_id', '$1', 'only_edit_button(n.notify_id, "master/notification/edit")');
    echo $this->datatables->generate();
  }
}