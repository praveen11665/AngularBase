<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class New_deals extends MY_Controller
{
  public function __construct()
  {
    parent::__construct();
    // Load language
    $this->lang->load("vendor_lang","english");
    $this->lang->load("setting_lang","english");
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
    if( $this->acl_permits('vendor.new_deals_waiting_for_approval') )
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
                        'list_title'  => $this->lang->line('new_deals_page_title'),
                        'view_title'  => $this->lang->line('product_view_details'),
                        'list_view'   => TRUE
                    );    
    //Table Config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('table_head_product_name'), lang('table_head_tbt_cut_points'),  lang('label_updated_on'), lang('label_updated_by'), lang('table_head_action'));
    $viewData['dataTableUrl']     = 'New_deals/datatable';

    $data = array (
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('new_deals_page_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
                  );
    $this->load->view($this->dbvars->app_template, $data);
  }

  //Take records and view to datatable
  function datatable()
  {
    $this->datatables->select('p.product_name, p.tbt_cut_points, p.updated_on, UCASE(u.username), p.product_id')
                     ->from('product as p')
                     ->join('users as u', 'u.user_id = p.user_id', 'left');
    $this->datatables->where('p.approved_status', '0');
    $this->datatables->where('p.is_delete', '0');
    $this->datatables ->edit_column('p.updated_on', '$1', 'get_date_timeformat(p.updated_on)');
    $this->datatables->edit_column('p.product_id', '$1','get_angular_view(p.product_id, "New_deals/angularViewForm", 1)');
    echo $this->datatables->generate();
  }

  /*
    Approve selected deal
    To be approve status updated
    In product approved status updated 1
  */
  public function approve($product_id='')
  {
    $where_array = array('product_id' => $product_id);
    //To be edit approved status as 1
    $productUpdate = $this->mcommon->common_edit('product', array('approved_status' => '1'), $where_array);    
  
    if($productUpdate)
    {
      //Log Approved vendor
      $productname      = $this->mcommon->specific_row_value('product',array('product_id' => $product_id),'product_name');
      $user_id          = $this->mcommon->specific_row_value('product',array('product_id' => $product_id),'user_id');
      $username         = $this->mcommon->specific_row_value('users',array('user_id' => $user_id), 'username');
      $app_activityArr  = array(
                                'log_timestamp'     => date('Y-m-d H:i:s'),
                                'activity_id'       => 6,
                                'log_activity'      => $username." ".$this->lang->line('deal_your')." ".$productname." ".$this->lang->line('ven_appr_by'),
                                'log_activity_link' => uri_string(),
                                'user_id'           => $user_id
                              );
      $this->mcommon->common_insert('activity_logs', $app_activityArr);

      //Send Push Notification with reasons
      $this->prefs->userNotify($user_id, '3', array('username' => $username));

      //Log for Disapproved vendor push notification
      $notifyArr = array(
                          'log_timestamp'     => date('Y-m-d H:i:s'),
                          'activity_id'       => 8,
                          'log_activity'      => $this->lang->line('ven_push_nofify')." ".$username,
                          'log_activity_link' => uri_string(),
                          'user_id'           => $user_id
                        );
      $this->mcommon->common_insert('activity_logs', $notifyArr);

      $this->session->set_flashdata('msg', 'Deal Approved Successfully');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('new_deals/add'));
    }
  }

  /*
    Disapprove the vendor based product_id
    Give the reason for disapprove vendors
  */
  public function disapprove($product_id='')
  {
    $reasonData     = array(
                              'product_id' => $product_id,
                              'reason'     => $this->input->post('reason')
                           );
    $reasonInsert  = $this->mcommon->common_insert('deals_reasons', $reasonData);
    $productUpdate = $this->mcommon->common_edit('product', array('approved_status' => '2'), array('product_id' => $product_id));

    if($reasonInsert || $productUpdate)
    {
      //Log for Disapproved vendor 
      $productname      = $this->mcommon->specific_row_value('product',array('product_id' => $product_id),'product_name');
      $user_id          = $this->mcommon->specific_row_value('product',array('product_id' => $product_id),'user_id');
      $username         = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
      $app_activityArr = array(
                                'log_timestamp'     => date('Y-m-d H:i:s'),
                                'activity_id'       => 7,
                                'log_activity'      => $productname." ".$this->lang->line('ven_dis_appr_by')." ".$this->input->post('reason'),
                                'log_activity_link' => uri_string(),
                                'user_id'           => $user_id
                             );
      $this->mcommon->common_insert('activity_logs', $app_activityArr);      

      $this->session->set_flashdata('msg', 'Deal Disapproved Successfully');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('new_deals/add'));
    }
  }

  //Load and view form with click on datatable row
  public function angularViewForm($product_id='')
  {
    /*APPROVE AND DISAPPROVE PRODUCTS */
    $formData['showButtons']    = TRUE;
    $formData['approveUrl']     = base_url('New_deals/approve/');
    $formData['disApproveUrl']  = base_url('New_deals/disapprove/');
    $formData['productData']    = $this->prefs->getProductDetails($product_id);
    $user_id                    = $this->mcommon->specific_row_value('product', array('product_id' => $product_id), 'user_id');
    $formData['activityData']   = $this->prefs->getactivityList($user_id);
    echo $this->load->view('angular_forms/product_details_form', $formData);
  }
}
?>