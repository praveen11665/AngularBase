<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class DocumentList extends MY_Controller 
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
    if( $this->acl_permits('master.document_add') )
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
    $formData['ActionUrl']     = 'master/DocumentList/formSubmit';  
    $viewData = array(
                        'form_title'        => $this->lang->line('label_document_create'),
                        'list_title'        => $this->lang->line('label_document_list_title'),
                        'form_view'         => $this->load->view('master/documentList_form', $formData, TRUE)
                      );    
    //Table Config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_document_name'), lang('label_document_is_mandatory'), lang('label_document_for'), lang('label_document_type'), lang('label_action'));

    $viewData['dataTableUrl']  = 'master/DocumentList/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('label_documenr_form_title'),
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
      $this->form_validation->set_rules('document_name', $this->lang->line('label_document_name'), 'required');         
      $this->form_validation->set_rules('document_for[]', $this->lang->line('label_document_for'), 'required'); 
      $this->form_validation->set_rules('document_type[]', $this->lang->line('label_document_type'), 'required'); 

      if($this->form_validation->run() == TRUE)
      {
        if($this->input->post('document_id') == "")
        {
          //Insert          
          $data       = array(                                              
                                'document_name'   => $this->input->post('document_name'),
                                'is_mandatory'    => ($this->input->post('is_mandatory'))?$this->input->post('is_mandatory'):'',
                                'document_for'    => implode(',', $this->input->post('document_for')),
                                'document_type'   => implode(',', $this->input->post('document_type')),
                                'created_on'      => date('Y-m-d H:i:s'),
                                'created_by'      => $this->auth_user_id,
                                'updated_by'      => $this->auth_user_id,
                              );
          $result     = $this->mcommon->common_insert('document', $data);

          if($result)
          {
            //Success Message After Insertion
            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/documentList/add'));
          }
        }
        else
        {
          //Update
          $data       = array(                                              
                                'document_name'   => $this->input->post('document_name'),
                                'is_mandatory'    => ($this->input->post('is_mandatory'))?$this->input->post('is_mandatory'):'',
                                'document_for'    => implode(',', $this->input->post('document_for')),
                                'document_type'   => implode(',', $this->input->post('document_type')),
                                'updated_on'      => date('Y-m-d H:i:s'),
                                'updated_by'      => $this->auth_user_id,
                              );
                     
          $where_array = array('document_id' => $this->input->post('document_id'));
          $result      = $this->mcommon->common_edit('document', $data, $where_array);
   
          if($result)
          {
            //Success Message After Update
            $this->session->set_flashdata('msg', 'Updated Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('master/documentList/add'));
          }
          else
          {
            //Message while Submitting Form Without Any Update
            $this->session->set_flashdata('msg', 'No Data Has Been Changed');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('master/documentList/add'));
          }  
        }
      }
    }
    $this->loadForm();
  } 

  //To be Edit the Data
  public function edit($document_id='')
  {
    echo $this->mcommon->row_records_all('document', array('document_id' => $document_id));
  }

  //To be Delete the Data
  public function delete($document_id='')
  {
    //Acl Permission For Delete
    if( $this->acl_permits('master.document_delete') )
    {
      $where_array = array('document_id' => $document_id);
      $result      = $this->mcommon->common_edit('document',array('is_delete' => '1'), $where_array);
      
      if($result)
      {
        $this->session->set_flashdata('msg', 'Deleted Successfully');
        $this->session->set_flashdata('alertType', 'success');
        redirect(base_url('master/documentList/add'));
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
    $this->datatables->select('d.document_name, d.is_mandatory, d.document_for, GROUP_CONCAT(DISTINCT dt.document_type) as documentType, d.document_id')
    ->from('document as d'); 
    $this->datatables->join('document_type as dt', 'FIND_IN_SET(dt.doc_type_id, d.document_type) != 0', 'left');
    $this->datatables->group_by('d.document_id');
    $this->datatables->where('is_delete','0');  
    $this->datatables->edit_column('d.document_id', '$1', 'only_edit_button(d.document_id, "master/DocumentList/edit")');
    $this->datatables->edit_column('d.document_for', '$1', 'getDocumentFor(d.document_for)');
    $this->datatables->edit_column('d.is_mandatory', '$1', 'getDocumentMandatory(d.is_mandatory)');
    echo $this->datatables->generate();
  }
}