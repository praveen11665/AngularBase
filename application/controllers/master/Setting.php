<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Setting extends MY_Controller 
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
    if( $this->acl_permits('master.setting_add') )
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
    //Page config
    $formData['ActionUrl']     = 'master/Setting/formSubmit'; 
    $formData['message']       = $this->session->flashdata('msg');
    $formData['alertType']     = $this->session->flashdata('alertType');
    $formData['settingData']   = $this->mcommon->records_all('web_settings');
    $formData['dataTableUrl']  = '';

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('label_city_form_title'),
                    'content'   =>  $this->load->view('master/setting_form', $formData, TRUE)
                 );
    $this->load->view($this->dbvars->app_template, $data);
  }

  //To be Submit the form with POST values. The form either Inset or Edit with the codtion
  public function formSubmit($viewData='')
  {
    if(!empty($_POST))
    {
      //Validation Rules
      $this->form_validation->set_rules('value[]', $this->lang->line('label_value'), 'required');     
      
      if($this->form_validation->run() == TRUE)
      {
        $keyArr   = $this->input->post('key');
        $valueArr = $this->input->post('value');

        foreach ($keyArr as $key => $p_key) 
        {
          $data      = array('key_value' => $valueArr[$key]);
          $result    = $this->mcommon->common_edit('web_settings', $data, array('ws_id' => $p_key));
          $success[] = $result;
        }

        if(in_array('1', $success))
        {         
          //Success Message After Update
          $this->session->set_flashdata('msg', 'Updated Successfully');
          $this->session->set_flashdata('alertType', 'success');
          redirect(base_url('master/setting/add'));           
        }
        else
        {
          //Message while Submitting Form Without Any Update
          $this->session->set_flashdata('msg', 'No Data Has Been Changed');
          $this->session->set_flashdata('alertType', 'danger');
          redirect(base_url('master/setting/add'));
        }
      }
    }
    $this->loadForm();
  }
}
