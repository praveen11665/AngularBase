<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Country extends MY_Controller 
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
    if( $this->acl_permits('master.country_add') )
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
    //Page config
    $formData['ActionUrl']     = 'master/Country/formSubmit';  
    $viewData = array(
                        'form_title'        => $this->lang->line('label_country_create'),
                        'list_title'        => $this->lang->line('label_country_list_title'),
                        'form_view'         => $this->load->view('master/country_form', $formData, TRUE)
                      );

    //Table config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_country_name'), lang('label_country_code'), lang('label_isd_code'), lang('label_status'), lang('label_action'));

    $viewData['dataTableUrl']  = 'master/Country/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('label_country_form_title'),
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
      $this->form_validation->set_rules('country_name', $this->lang->line('label_country_name'), 'required');         
      $this->form_validation->set_rules('country_code', $this->lang->line('label_country_code'), 'required|trim');
      $this->form_validation->set_rules('isd_code', $this->lang->line('label_isd_code'), 'required|trim');      
      
      if($this->form_validation->run() == TRUE)
      {
        if($this->input->post('country_id') == "")
        {
          //Insert
          $data       = array(                                              
                                'country_name'   => $this->input->post('country_name'),
                                'country_code'   => $this->input->post('country_code'),
                                'isd_code'       => $this->input->post('isd_code'),
                                'status'         => ($this->input->post('status'))?$this->input->post('status'):'',
                                'created_on'     => date('Y-m-d H:i:s'),
                                'created_by'     => $this->auth_user_id,
                                'updated_by'     => $this->auth_user_id,
                              );
          $result     = $this->mcommon->common_insert('countries', $data);

          if($result)
          {
            //Success Message After Insertion
            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/country/add'));
          }
        }
        else
        {
          //Update
          $data       = array(                                              
                                'country_name'   => $this->input->post('country_name'),
                                'country_code'   => $this->input->post('country_code'),
                                'isd_code'       => $this->input->post('isd_code'),
                                'status'         => ($this->input->post('status'))?$this->input->post('status'):'',
                                'updated_by'     => $this->auth_user_id,
                              );
                     
          $where_array = array('country_id' => $this->input->post('country_id'));
          $result      = $this->mcommon->common_edit('countries', $data, $where_array);
   
          if($result)
          {
            //Success Message After Update
            $this->session->set_flashdata('msg', 'Updated Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/country/add'));
          }
          else
          {
            //Message while Submitting Form Without Any Update
            $this->session->set_flashdata('msg', 'No Data Has Been Changed');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('master/country/add'));
          }  
        }
      }
    }
    $this->loadForm();
  } 

  //To be Edit the Data
  public function edit($country_id='')
  {
    echo $this->mcommon->row_records_all('countries', array('country_id' => $country_id));
  }

  //To be Delete the Data
  public function delete($country_id='')
  {
    //Acl Permission For Delete
    if( $this->acl_permits('master.country_delete') )
    {
      $where_array = array('country_id' => $country_id);
      $result      = $this->mcommon->common_delete('countries',$where_array);
      
      if($result)
      {
        $this->session->set_flashdata('msg', 'Deleted Successfully');
        $this->session->set_flashdata('alertType', 'success');
        redirect(base_url('master/country/add'));
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
    $this->datatables ->select('c.country_name, c.country_code, c.isd_code, c.status, c.country_id', FALSE)
    ->from('countries as c');   
    $this->datatables->where('c.country_id !=', '0');                                    
    $this->datatables->edit_column('c.status', '$1', 'getStatus(c.status)');
    $this->datatables->edit_column('c.country_id', '$1','only_edit_button(c.country_id, "master/Country/edit")');
    echo $this->datatables->generate();
  }
}