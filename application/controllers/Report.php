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
    // Check the user is loggedin or not
    $this->is_logged_in();
  }

  //Registration Chart Report
  public function registration()
  {
    if( $this->acl_permits('report.registration_chart') )
    {
      /* UNSET SET NEW SESSION DATA FROM SEARCH FILTERS */
      $this->session->unset_userdata('from_date');
      $this->session->unset_userdata('to_date');
      $this->session->unset_userdata('country_id');
      $this->session->unset_userdata('state_id');
      $this->session->unset_userdata('city_id');
      $this->session->unset_userdata('status_id');
      
      if(!empty($_POST))
      {
        $searchData = array(
                              'from_date'     =>   $this->input->post('from_date'),
                              'to_date'       =>   $this->input->post('to_date'),
                              'country_id'    =>   $this->input->post('country_id'),                  
                              'state_id'      =>   $this->input->post('state_id'),                  
                              'city_id'       =>   $this->input->post('city_id'),                  
                              'status_id'     =>   $this->input->post('status_id'),                  
                            );
        $this->session->set_userdata($searchData);
      }

      //Load Search form with filterArray
      $searchData['ActionUrl']    =  'report/registration';
      $searchData['filterArr']    =  array('from_date', 'to_date', 'country', 'state', 'newstatus'); 

      $viewData = array(
                          'report_view'       => TRUE,
                          'list_title'        => $this->lang->line('title_regchart_report'),
                          'search_title'      => $this->lang->line('title_search_filters'),
                          'search_view'       => $this->load->view('common_search_form', $searchData, TRUE)
                        );
      //Table Config
      $tmpl = array ('table_open'  => '<table id="reportTable" cellpadding="2" cellspacing="1" class="table table-striped">' );
      $this->table->set_template($tmpl); 
      $this->table->set_heading(lang('table_head_username'), lang('lable_role_name'), lang('table_head_company_name'), lang('label_company_address'), lang('table_cont_name'),lang('table_head_contact_number'), lang('table_head_registered_on'), lang('table_approved_on'), lang('lable_head_reason'), lang('label_status'));

      $viewData['dataTableUrl']     = 'Report/registrationDatatable';
      $data = array (
                      'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('reg_chart_report_title'),
                      'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
                    );
      $this->load->view($this->dbvars->app_template, $data);
    }else
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
  function registrationDatatable()
  {
    /* CHECK IF SESSION DATA ARE THERE NOT, IF THERE SET WHERE CONDITION FOR THAT DATA */
    $from_date     = ($this->session->userdata('from_date')) ? $this->session->userdata('from_date') : '-1';
    $to_date       = ($this->session->userdata('to_date')) ? $this->session->userdata('to_date') :  '-1';
    $country_id    = ($this->session->userdata('country_id')) ? $this->session->userdata('country_id') : '-1';
    $state_id      = ($this->session->userdata('state_id')) ? $this->session->userdata('state_id') : '-1';
    $city_id       = ($this->session->userdata('city_id')) ? $this->session->userdata('city_id') : '-1';

    if($this->session->userdata('status_id') == '0')
    {
      $status_id = '0';
    }else
    {
      $status_id     = ($this->session->userdata('status_id')) ? $this->session->userdata('status_id') : '-1';       
    }

    $this->datatables->select('u.username, ar.role_name, c.company_name, CONCAT(c.address, "<br/>", ci.name, ",", s.name, ",", co.country_name)as address, c.cont_first_name, c.cont_number, d.updated_on, up.approved_on, dr.reason, rs.status')
           ->from('users as u')
           ->join('app_roles as ar', 'ar.role_id = u.auth_level', 'left')
           ->join('company as c', 'u.user_id = c.user_id', 'left')
           ->join('countries as co', 'co.country_id = c.country', 'left')
           ->join('states as s', 's.state_id = c.state', 'left')
           ->join('cities as ci', 'ci.city_id = c.city', 'left')
           ->join('upload_documents as d','u.user_id = d.user_id', 'left')                   
           ->join('user_profile as up','up.user_id = u.user_id', 'left')
           ->join('registration_status as rs','rs.reg_id = up.approved_status', 'left')
           ->join('disapprove_reasons as dr','dr.user_id = u.user_id', 'left');
    $whereArray = array('up.profile_status' => '1', 'up.document_status' => '1');
    $this->datatables->where($whereArray);
    $this->db->where_in('u.auth_level', explode(",", '4,5'));

    if ($from_date != '-1' && $to_date !='-1') 
    {
      $this->datatables->where('DATE(d.updated_on) >=', date('Y-m-d', strtotime($from_date)));
      $this->datatables->where('DATE(d.updated_on) <=', date('Y-m-d', strtotime($to_date)));
    }

    if($country_id != '' && $country_id != '-1')
    {
      $this->datatables->where('c.country', $country_id);
    }

    if($state_id != '' && $state_id != '-1')
    {
      $this->datatables->where('c.state', $state_id);
    }

    if($city_id != '' && $city_id != '-1')
    {
      $this->datatables->where('c.city', $city_id);
    }       

    if($status_id != '' && $status_id != '-1')
    {
      $this->datatables->where('up.approved_status', $status_id);
    }else if ($status_id == '0') 
    {
      $this->datatables->where('up.approved_status', '0');
    }  

    $this->datatables ->edit_column('d.updated_on', '$1', 'get_date_timeformat(d.updated_on)');
    $this->datatables ->edit_column('up.approved_on', '$1', 'get_date_timeformat(up.approved_on)');
    $this->datatables->edit_column('rs.status', '$1','getReportStatus(rs.status)');
    $this->datatables->edit_column('ar.role_name', '$1','getUserRoles(ar.role_name)');
    $this->datatables->edit_column('dr.reason', '$1','getReason(dr.reason)');
    $this->datatables->group_by('u.user_id');
    echo $this->datatables->generate();
  }

  //Deal List Report
  public function dealList()
  {
    if( $this->acl_permits('report.deal_list') )
    {
      /* UNSET SET NEW SESSION DATA FROM SEARCH FILTERS */
      $this->session->unset_userdata('from_date');
      $this->session->unset_userdata('to_date');
      $this->session->unset_userdata('user_id');
      $this->session->unset_userdata('product_id');
      $this->session->unset_userdata('status_id');
      
      if(!empty($_POST))
      {
        $searchData = array(
                              'from_date'     =>   $this->input->post('from_date'),
                              'to_date'       =>   $this->input->post('to_date'),
                              'user_id'       =>   $this->input->post('user_id'),                  
                              'product_id'    =>   $this->input->post('product_id'),                  
                              'status_id'     =>   $this->input->post('status_id'),                  
                            );
        $this->session->set_userdata($searchData);
      }

      //Load Search form with filterArray
      $searchData['ActionUrl']    =  'report/dealList';
      $searchData['filterArr']    =  array('from_date', 'to_date', 'users', 'products', 'newstatus'); 

      $viewData = array(
                          'report_view'       => TRUE,
                          'list_title'        => $this->lang->line('title_deal_list_title'),
                          'search_title'      => $this->lang->line('title_search_filters'),
                          'search_view'       => $this->load->view('common_search_form', $searchData, TRUE)
                        );
      //Table Config
      $tmpl = array ('table_open'  => '<table id="reportTable" cellpadding="2" cellspacing="1" class="table table-striped">' );
      $this->table->set_template($tmpl); 
      $this->table->set_heading(lang('table_head_product_name'), lang('table_head_tbt_cut_points'), lang('table_head_density'), lang('table_head_viscosity'), lang('label_report_updated_on'), lang('label_seller_name'), lang('lable_head_reason'), lang('label_status'));
      $viewData['dataTableUrl']     = 'Report/dealDatatable';

      $data = array (
                      'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('deal_list_report_title'),
                      'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
                    );
      $this->load->view($this->dbvars->app_template, $data);
    }else
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
     
    $this->datatables->select('p.product_name, p.tbt_cut_points, p.density, p.viscosity, p.updated_on, UCASE(u.username), dr.reason, rs.status')
     ->from('product as p');
    $this->datatables->join('users as u', 'p.user_id = u.user_id','left');     
    $this->datatables->join('registration_status as rs','rs.reg_id = p.approved_status', 'left');
    $this->datatables->join('deals_reasons as dr','dr.product_id = p.product_id', 'left');
            
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
    $this->datatables->edit_column('dr.reason', '$1','getReason(dr.reason)');
    echo $this->datatables->generate();
  }

  //Deal List Report
  public function transaction()
  {
    if( $this->acl_permits('report.transaction') )
    {
      /* UNSET SET NEW SESSION DATA FROM SEARCH FILTERS */
      $this->session->unset_userdata('from_date');
      $this->session->unset_userdata('to_date');
      $this->session->unset_userdata('user_id');
      $this->session->unset_userdata('product_id');
      $this->session->unset_userdata('buyer_id');
      $this->session->unset_userdata('status_id');
      
      if(!empty($_POST))
      {
        $searchData = array(
                              'from_date'     =>   $this->input->post('from_date'),
                              'to_date'       =>   $this->input->post('to_date'),
                              'user_id'       =>   $this->input->post('user_id'),
                              'product_id'    =>   $this->input->post('product_id'),
                              'buyer_id'      =>   $this->input->post('buyer_id'),
                              'status_id'     =>   $this->input->post('status_id')
                           );
        $this->session->set_userdata($searchData);
      }

      //Load Search form with filterArray
      $searchData['ActionUrl']    =  'report/transaction';
      $searchData['filterArr']    =  array('from_date', 'to_date', 'users', 'products', 'buyers', 'newstatus'); 

      $viewData = array(
                          'report_view'       => TRUE,
                          'list_title'        => $this->lang->line('title_trasaction_report'),
                          'search_title'      => $this->lang->line('title_search_filters'),
                          'search_view'       => $this->load->view('common_search_form', $searchData, TRUE)
                        );
      //Table Config
      $tmpl = array ('table_open'  => '<table id="reportTable" cellpadding="2" cellspacing="1" class="table table-striped">' );
      $this->table->set_template($tmpl); 
      $this->table->set_heading(lang('table_head_product_name'), lang('table_head_deal_date'), lang('label_sellers'), lang('label_buyer'),lang('lable_request_date'), lang('lable_head_reason'), lang('label_status'));
      $viewData['dataTableUrl']     = 'Report/transactionDatatable';

      $data = array (
                      'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('title_trasaction_report_f'),
                      'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
                    );
      $this->load->view($this->dbvars->app_template, $data);
    }else
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
  function transactionDatatable()
  {
    /* CHECK IF SESSION DATA ARE THERE NOT, IF THERE SET WHERE CONDITION FOR THAT DATA */
    $from_date     = ($this->session->userdata('from_date')) ? $this->session->userdata('from_date') : '-1';
    $to_date       = ($this->session->userdata('to_date')) ? $this->session->userdata('to_date') :  '-1';
    $seller_id     = ($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : '-1';
    $product_id    = ($this->session->userdata('product_id')) ? $this->session->userdata('product_id') : '-1'; 
    $buyer_id      = ($this->session->userdata('buyer_id')) ? $this->session->userdata('buyer_id') : '-1';

    if($this->session->userdata('status_id') == '0')
    {
      $status_id = '0';
    }else
    {
      $status_id     = ($this->session->userdata('status_id')) ? $this->session->userdata('status_id') : '-1';       
    }
     
    $this->datatables->select('p.product_name, dr.deal_date, u.username, us.username as buyer, dr.request_date, dr.reason, rs.status')
                     ->from('deal_request as dr');
    $this->datatables->join('product as p', 'p.product_id = dr.product_id','left'); 
    $this->datatables->join('users as u', 'u.user_id = dr.seller_id','left'); 
    $this->datatables->join('users as us', 'us.user_id = dr.buyer_id','left');  
    $this->datatables->join('registration_status as rs','rs.reg_id = dr.status', 'left');

    if ($from_date != '-1' && $to_date !='-1') 
    {
      $this->datatables->where('DATE(dr.deal_date) >=', date('Y-m-d', strtotime($from_date)));
      $this->datatables->where('DATE(dr.deal_date) <=', date('Y-m-d', strtotime($to_date)));
    }
     if($seller_id != '' && $seller_id != '-1')
    {
      $this->datatables->where('dr.seller_id', $seller_id);
    }

    if($product_id != '' && $product_id != '-1')
    {
      $this->datatables->where('dr.product_id', $product_id);
    }
    if($buyer_id != '' && $buyer_id != '-1')
    {
      $this->datatables->where('dr.buyer_id', $buyer_id);
    }

    if($status_id != '' && $status_id != '-1')
    {
      $this->datatables->where('dr.status', $status_id);
    }else if ($status_id == '0') 
    {
      $this->datatables->where('dr.status', '0');
    } 

    $this->datatables->edit_column('dr.deal_date', '$1', 'get_date_timeformat(dr.deal_date)');
    $this->datatables->edit_column('dr.request_date', '$1', 'get_date_timeformat(dr.request_date)');
    $this->datatables->edit_column('rs.status', '$1','getReportStatus(rs.status)');
    $this->datatables->edit_column('dr.reason', '$1','getReason(dr.reason)');
    echo $this->datatables->generate();
  }
}