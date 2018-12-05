<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class City extends MY_Controller 
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
    if( $this->acl_permits('master.city_add') )
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
    $formData['ActionUrl']      = 'master/City/formSubmit';
    $viewData = array(
                        'form_title'        => $this->lang->line('label_city_create'),
                        'list_title'        => $this->lang->line('label_city_list_title'),
                        'form_view'         => $this->load->view('master/city_form', $formData, TRUE)
                      );
    //Table config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_country_name'), lang('label_state_name'), lang('label_city_name'), lang('label_status'), lang('label_action'));
    $viewData['dataTableUrl']  = 'master/City/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('label_city_form_title'),
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
      $this->form_validation->set_rules('country_id', $this->lang->line('label_country_name'), 'required');     
      $this->form_validation->set_rules('state_id', $this->lang->line('label_state_name'), 'required');          
      $this->form_validation->set_rules('city_name', $this->lang->line('label_city_name'), 'required|trim'); 
      
      if($this->form_validation->run() == TRUE)
      {
        if($this->input->post('city_id') == "")
        {
          //Insert
          $data   = array(
                            'state_id'        => $this->input->post('state_id'), 
                            'name'            => $this->input->post('city_name'),
                            'status'          => ($this->input->post('status'))?$this->input->post('status'):'',
                            'created_on'      => date('Y-m-d H:i:s'),
                            'created_by'      => $this->auth_user_id,
                            'updated_by'      => $this->auth_user_id,
                          );
          $result = $this->mcommon->common_insert('cities', $data);

          //To be edit the country as state based
          $this->mcommon->common_edit('states', array('country_id' => $this->input->post('country_id')), array('state_id' => $this->input->post('state_id')));

          if($result)
          {
            //Success Message After Insertion
            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/city/add'));
          }
        }
        else
        {
          //Update
          $data       = array(   
                                'state_id'         => $this->input->post('state_id'),                    
                                'name'            => $this->input->post('city_name'),
                                'status'          => ($this->input->post('status'))?$this->input->post('status'):'',
                                'updated_by'      => $this->auth_user_id,
                              );
                     
          $where_array = array('city_id' => $this->input->post('city_id'));
          $result      = $this->mcommon->common_edit('cities', $data, $where_array);

          //To be edit the country as state based
          $this->mcommon->common_edit('states', array('country_id' => $this->input->post('country_id')), array('state_id' => $this->input->post('state_id')));
   
          if($result)
          {
            //Success Message After Update
            $this->session->set_flashdata('msg', 'Updated Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/city/add'));
          }
          else
          {
            //Message while Submitting Form Without Any Update
            $this->session->set_flashdata('msg', 'No Data Has Been Changed');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('master/city/add'));
          }  
        }
      }
    }
    $this->loadForm();
  } 

  //To be Edit the Data
  public function edit($city_id='')
  {
    echo $this->prefs->getCityData($city_id);
  }

  //To be Delete the Data
  public function delete($city_id='')
  {
    //Acl Permission For Delete
    if( $this->acl_permits('master.city_delete') )
    {
      $where_array = array('city_id' => $city_id);
      $result      = $this->mcommon->common_delete('cities',$where_array);
      
      if($result)
      {
        $this->session->set_flashdata('msg', 'Deleted Successfully');
        $this->session->set_flashdata('alertType', 'success');
        redirect(base_url('master/city/add'));
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
    $this->datatables ->select('co.country_name, s.name as state_name, c.name as city_name, c.status, c.city_id')
                      ->from('cities as c')
                      ->join('states as s', 'c.state_id = s.state_id AND s.status = 1', 'left')
                      ->join('countries as co', 'co.country_id = s.country_id AND co.status = 1', 'left'); 
    $this->db->order_by('co.country_name', 'ASC');
    $this->db->where('co.country_name !=', 'NULL');
    $this->datatables->edit_column('c.city_id', '$1', 'only_edit_button(c.city_id, "master/city/edit")');
    $this->datatables->edit_column('c.status', '$1', 'getStatus(c.status)');
    echo $this->datatables->generate();
  }
}
