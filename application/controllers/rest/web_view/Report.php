<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Report extends MY_Controller 
{
  public function __construct()
  {
    parent::__construct();        
    // Load language
    $this->lang->load("vendor_lang","english");
    // Load Form and Form Validation
    $this->load->helper('form');
    $this->load->library('form_validation');
  }

  //Deal List Report
  public function dealList($user_id='')
  {
    if($user_id)
    { 
      /* UNSET SET NEW SESSION DATA FROM SEARCH FILTERS */
      $this->session->unset_userdata('from_date');
      $this->session->unset_userdata('to_date');
      $this->session->unset_userdata('user_id');
      $this->session->unset_userdata('product_id');
      $this->session->unset_userdata('status_id');

      $this->session->set_userdata(array('user_id' => $user_id));

      if(!empty($_POST))
      {
        $searchData = array(
                              'from_date'     =>   $_POST['from_date'],
                              'to_date'       =>   $_POST['to_date'],
                              'product_id'    =>   $_POST['product_id'],                  
                              'status_id'     =>   $_POST['status_id'],                  
                            );
        $this->session->set_userdata($searchData);
      }

      //Load Search form with filterArray
      $searchData['ActionUrl']    =  'rest/web_view/Report/dealList/'.$user_id;
      $searchData['filterArr']    =  array('from_date', 'to_date', 'products', 'newstatus'); 

      $viewData = array(
                          'report_view'       => TRUE,
                          'list_title'        => $this->lang->line('title_deal_list_title'),
                          'search_title'      => $this->lang->line('title_search_filters'),
                          'search_view'       => $this->load->view('common_search_form', $searchData, TRUE)
                        );
      //Table Config
      $tmpl = array ('table_open'  => '<table id="reportTable" cellpadding="2" cellspacing="1" class="table table-striped">' );
      $this->table->set_template($tmpl); 
      $this->table->set_heading(lang('table_head_product_name'), lang('table_head_tbt_cut_points'), lang('table_head_density'), lang('table_head_viscosity'),lang('table_head_user_agreement_terms'), lang('label_report_updated_on'), lang('label_seller_name'), lang('label_status'));
      $viewData['dataTableUrl']    = 'rest/web_view/Report/dealDatatable';

      $this->load->view('base/web_view_template', $viewData);
    }else
    {
      $data['status']  = '200';
      $data['code']    = 'error';
      $data['message'] = 'Please Given User Id';

      echo json_encode(array($data));
    }
  }

  //Take records and view to datatable
  function dealDatatable()
  {
    /* CHECK IF SESSION DATA ARE THERE NOT, IF THERE SET WHERE CONDITION FOR THAT DATA */
    $from_date     = ($this->session->userdata('from_date')) ? $this->session->userdata('from_date') : '-1';
    $to_date       = ($this->session->userdata('to_date')) ? $this->session->userdata('to_date') :  '-1';
    $user_id       = ($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : '-1';
    $product_id    = ($this->session->userdata('product_id')) ? $this->session->userdata('product_id') : '-1'; 
    if($this->session->userdata('status_id') == '0')
    {
      $status_id = '0';
    }else
    {
      $status_id     = ($this->session->userdata('status_id')) ? $this->session->userdata('status_id') : '-1';       
    }
     
    $this->datatables->select('p.product_name, p.tbt_cut_points, p.density, p.viscosity, p.user_agreement_terms, p.updated_on, UCASE(u.username), rs.status')
     ->from('product as p');
    $this->datatables->join('users as u', 'p.user_id = u.user_id','left');     
    $this->datatables->join('registration_status as rs','rs.reg_id = p.approved_status', 'left');
            
    $this->datatables->where('p.is_delete', '0');

    if ($from_date != '-1' && $to_date !='-1') 
    {
      $this->datatables->where('DATE(p.updated_on) >=', date('Y-m-d', strtotime($from_date)));
      $this->datatables->where('DATE(p.updated_on) <=', date('Y-m-d', strtotime($to_date)));
    }
     if($user_id != '' && $user_id != '-1')
    {
      $this->datatables->where('p.user_id', $user_id);
    }

    if($product_id != '' && $product_id != '-1')
    {
      $this->datatables->where('p.product_id', $product_id);
    }

    if($status_id != '' && $status_id != '-1')
    {
      $this->datatables->where('p.approved_status', $status_id);
    }else if ($status_id == '0') 
    {
      $this->datatables->where('p.approved_status', '0');
    } 

    $this->datatables->edit_column('p.updated_on', '$1', 'get_date_timeformat(p.updated_on)');
    $this->datatables->edit_column('rs.status', '$1','getReportStatus(rs.status)');
    echo $this->datatables->generate();
  }
}