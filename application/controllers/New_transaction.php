<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class New_transaction extends MY_Controller
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
    if( $this->acl_permits('vendor.new_transactions') )
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
                       'list_title'    => $this->lang->line('new_transaction_page_title'),
                       'list_view'     => TRUE,
                       'view_title'    => $this->lang->line('request_view_details'),
                    );    
    //Table Config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('table_head_product_name'), lang('table_head_deal_date'),  lang('label_buyer'), lang('label_sellers'), lang('table_head_action'));
    $viewData['dataTableUrl']     = 'New_transaction/datatable';

    $data = array (
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('new_transaction_page_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
                  );
    $this->load->view($this->dbvars->app_template, $data);
  }

  //Take records and view to datatable
  function datatable()
  {
    $this->datatables->select('p.product_name, dr.deal_date, u.username, us.username as sellerName, dr.deal_request_id')
                     ->from('deal_request as dr')
                     ->join('product as p', 'p.product_id = dr.product_id', 'left')
                     ->join('users as u', 'u.user_id = dr.buyer_id', 'left')
                     ->join('users as us', 'us.user_id = dr.seller_id', 'left');
    $this->datatables->where('dr.status', '0');
    $this->datatables ->edit_column('dr.deal_date', '$1', 'get_date_timeformat(dr.deal_date)');
    $this->datatables->edit_column('dr.deal_request_id', '$1','get_angular_view(dr.deal_request_id, "New_transaction/angularViewForm", 1)');
    echo $this->datatables->generate();
  }

  /*
    Approve selected deal
    To be approve status updated
    In product approved status updated 1
  */
  public function approve($deal_request_id='')
  {
    $where_array = array('deal_request_id' => $deal_request_id);
    //To be edit approved status as 1
    $productUpdate = $this->mcommon->common_edit('deal_request', array('status' => '1'), $where_array);
  
    if($productUpdate=TRUE)
    {
      //Log Approved vendor
      $dealRequestArr = $this->mcommon->records_all('deal_request', $where_array);

      foreach ($dealRequestArr as $row) 
      {
        $buyer_id     = $row->buyer_id;
        $seller_id    = $row->seller_id;
        $product_id   = $row->product_id;
      }

      $buyerName      = $this->mcommon->specific_row_value('users',array('user_id' => $buyer_id), 'username');
      $producrName    = $this->mcommon->specific_row_value('product',array('product_id' => $product_id), 'product_name');
      $sellerName     = $this->mcommon->specific_row_value('users',array('user_id' => $seller_id), 'username');

      $app_activityArr  = array(
                                'log_timestamp'     => date('Y-m-d H:i:s'),
                                'activity_id'       => 6,
                                'log_activity'      => $buyerName." ".$this->lang->line('deal_request_product')." ".$producrName,
                                'log_activity_link' => uri_string(),
                                'user_id'           => $seller_id
                              );
      $this->mcommon->common_insert('activity_logs', $app_activityArr);

      //Send Push Notification with reasons
      $this->prefs->userNotify($seller_id, '6', array('buyer_name' => $buyerName));

      //Log for Disapproved vendor push notification
      $notifyArr = array(
                          'log_timestamp'     => date('Y-m-d H:i:s'),
                          'activity_id'       => 8,
                          'log_activity'      => $this->lang->line('ven_push_nofify')." ".$sellerName,
                          'log_activity_link' => uri_string(),
                          'user_id'           => $seller_id
                        );
      $this->mcommon->common_insert('activity_logs', $notifyArr);

      $this->session->set_flashdata('msg', 'Deal Approved Successfully');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('new_transaction/add'));
    }
  }

  /*
    Disapprove the vendor based product_id
    Give the reason for disapprove vendors
  */
  public function disapprove($deal_request_id='')
  {
    $reasonData     = array(
                              'deal_request_id' => $deal_request_id,
                              'reason'          => $this->input->post('reason')
                           );
    $productUpdate = $this->mcommon->common_edit('deal_request', array('status' => '2', 'reason' => $this->input->post('reason')), array('deal_request_id' => $deal_request_id));

    if($reasonInsert || $productUpdate)
    {      
      //Log for Disapproved vendor
      $dealRequestArr = $this->mcommon->records_all('deal_request', $where_array);

      foreach ($dealRequestArr as $row) 
      {
        $buyer_id     = $row->buyer_id;
        $seller_id    = $row->seller_id;
        $product_id   = $row->product_id;
      } 

      $buyerName      = $this->mcommon->specific_row_value('users',array('user_id' => $buyer_id), 'username');
      $producrName    = $this->mcommon->specific_row_value('product',array('product_id' => $product_id), 'product_name');
      $sellerName     = $this->mcommon->specific_row_value('users',array('user_id' => $seller_id), 'username');

      $app_activityArr = array(
                                'log_timestamp'     => date('Y-m-d H:i:s'),
                                'activity_id'       => 7,
                                'log_activity'      => $buyerName." ".$this->lang->line('deal_request_disapproved')." ".$producrName." ".$this->lang->line('reason_for')." ".$this->input->post('reason'),
                                'log_activity_link' => uri_string(),
                                'user_id'           => $seller_id
                             );
      $this->mcommon->common_insert('activity_logs', $app_activityArr);

      //Send Push Notification with reasons
      $this->prefs->userNotify($seller_id, '7', array('buyer_name' => $buyerName));

      //Log for Disapproved vendor push notification
      $notifyArr = array(
                          'log_timestamp'     => date('Y-m-d H:i:s'),
                          'activity_id'       => 8,
                          'log_activity'      => $this->lang->line('ven_push_nofify')." ".$sellerName,
                          'log_activity_link' => uri_string(),
                          'user_id'           => $seller_id
                        );
      $this->mcommon->common_insert('activity_logs', $notifyArr);

      $this->session->set_flashdata('msg', 'Deal Disapproved Successfully');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('new_transaction/add'));
    }
  }

  //Load and view form with click on datatable row
  public function angularViewForm($deal_request_id='')
  {
    //TAKEN SELLER ID BASED ON DEAL REQUEST ID
    $seller_id = $this->mcommon->specific_row_value('deal_request', array('deal_request_id' => $deal_request_id), 'seller_id');
    /*APPROVE AND DISAPPROVE PRODUCTS */
    $formData['showButtons']    = TRUE;
    $formData['approveUrl']     = base_url('new_transaction/approve/');
    $formData['disApproveUrl']  = base_url('new_transaction/disapprove/');
    $formData['productData']    = $this->prefs->getDealtransDetails($deal_request_id);
    $formData['activityData']   = $this->prefs->getactivityList($seller_id);
    echo $this->load->view('angular_forms/new_transaction_form', $formData);
  }
}
?>