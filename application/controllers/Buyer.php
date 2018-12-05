<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Buyer extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    // Load language
    $this->lang->load("vendor_lang","english");
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
    if( $this->acl_permits('vendor.buyer_waiting_for_approval') )
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
  public function loadForm($viewData=array())
  {
    //Page Config
    $viewData = array(
                       'list_title'   => $this->lang->line('buyer_page_title'),
                       'list_view'    => TRUE,
                       'view_title'   => $this->lang->line('vendor_view_details'),
                    );    
    //Table Config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('table_head_username'), lang('table_head_company_name'), lang('table_cont_name'), lang('table_head_contact_number'), lang('table_head_registered_on'), lang('label_action'));
    $viewData['dataTableUrl']     = 'Buyer/datatable';

    $data = array (
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('buyer_page_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
                  );
    $this->load->view($this->dbvars->app_template, $data);
  }

  //Take records and view to datatable
  function datatable()
  {
    $this->datatables->select('u.username, c.company_name, c.cont_first_name, c.cont_number, d.updated_on, u.user_id')
                     ->from('users as u')
                     ->join('company as c', 'u.user_id = c.user_id', 'left')
                     ->join('upload_documents as d','u.user_id = d.user_id', 'left')
                     ->join('user_profile as up','up.user_id = u.user_id', 'left');
    $whereArray = array('u.auth_level' => '5', 'u.banned' => '1', 'up.profile_status' => '1', 'up.document_status' => '1', 'up.approved_status' => '0');
    $this->datatables->where($whereArray);
    $this->datatables->group_by('u.user_id');
    $this->datatables ->edit_column('d.updated_on', '$1', 'get_date_timeformat(d.updated_on)');
    $this->datatables->edit_column('u.user_id', '$1','get_angular_view(u.user_id, "Buyer/angularViewForm", 1)');

    echo $this->datatables->generate();
  }

  /*
    Approve selected vendor
    To be approve two status uodated
    In user table banned update as 0
    In user profile approved status updated 1
  */
  public function approve($user_id='')
  {
    $where_array   = array('user_id' => $user_id);
    //To be edit banned user as unbanned users and approved status as 1
    $profileUpdate = $this->mcommon->common_edit('user_profile', array('approved_status' => '1', 'approved_on' => date('Y-m-d H:i:s')), $where_array);
    $userUnbanned  = $this->mcommon->common_edit('users', array('banned' => '0'), $where_array);

    if($profileUpdate || $userUnbanned)
    {
      //Log Approved vendor
      $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
      $app_activityArr = array(
                              'log_timestamp'     => date('Y-m-d H:i:s'),
                              'activity_id'       => 6,
                              'log_activity'      => $username." ".$this->lang->line('ven_appr_by'),
                              'log_activity_link' => uri_string(),
                              'user_id'           => $user_id
                          );
      $this->mcommon->common_insert('activity_logs', $app_activityArr);

      $this->session->set_flashdata('msg', 'Vendor Approved Successfully');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('buyer/add'));
    }
  }

  /*
    Disapprove the vendor based user_id 
    Give the reason for disapprove vendors
  */ 

  public function disapprove($user_id='')
  {
    $reasonData     = array(
                              'user_id' => $user_id,
                              'reason'  => $this->input->post('reason')
                           );
    $reasonInsert  = $this->mcommon->common_insert('disapprove_reasons', $reasonData);
    $profileUpdate = $this->mcommon->common_edit('user_profile', array('approved_status' => '2', 'approved_on' => date('Y-m-d H:i:s'), 'user_device' => ''), array('user_id' => $user_id));

    //Send Push Notification with reasons
    $this->prefs->userNotify($user_id, '2', array('reason' => $this->input->post('reason')));

    if($reasonInsert || $profileUpdate)
    {
      //Disapproved by admin to be remove documents and company details
      $this->mcommon->common_delete('company', array('user_id' => $user_id));
      $this->mcommon->common_delete('upload_documents', array('user_id' => $user_id));
      
      //Log for Disapproved vendor 
      $username        = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
      $app_activityArr = array(
                              'log_timestamp'     => date('Y-m-d H:i:s'),
                              'activity_id'       => 7,
                              'log_activity'      => $username." ".$this->lang->line('ven_dis_appr_by')." ".$this->input->post('reason'),
                              'log_activity_link' => uri_string(),
                              'user_id'           => $user_id
                          );
      $this->mcommon->common_insert('activity_logs', $app_activityArr);
      
      //Log for Disapproved vendor push notification
      $notifyArr = array(
                              'log_timestamp'     => date('Y-m-d H:i:s'),
                              'activity_id'       => 8,
                              'log_activity'      => $this->lang->line('ven_push_nofify')." ".$username,
                              'log_activity_link' => uri_string(),
                              'user_id'           => $user_id
                          );
      $this->mcommon->common_insert('activity_logs', $notifyArr);

      $this->session->set_flashdata('msg', 'Vendor Disapproved Successfully');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('buyer/add'));
    }
  }

  //Load and view form with click on datatable row
  public function angularViewForm($user_id='')
  {
    /*APPROVE AND DISAPPROVE VENDORS */
    $formData['showButtons']    = TRUE;
    $formData['approveUrl']     = base_url('buyer/approve/');
    $formData['disApproveUrl']  = base_url('buyer/disapprove/');
    $formData['userData']       = $this->prefs->getVendorDetails($user_id);
    $formData['activityData']   = $this->prefs->getactivityList($user_id);
    echo $this->load->view('angular_forms/vendor_detail_form', $formData);
  }
}
?>