<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Transaction_list extends MY_Controller
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
	    // acl permission access for add
	    if( $this->acl_permits('vendor.transaction_list') )
	    {
	      $this->loadForm();
	    }          
	    else // Unauthorized access view
	    {
	      $view_data='';
	      $data = array(
	                      'title'     =>  $this->lang->line('unauth_page_title'),
	                      'content'   =>  $this->load->view('unauthorized',$view_data,TRUE)
	                    );
	      $this->load->view('base/error_template', $data);
	     }
  	}

  	//To be load the form with array values
	public function loadForm($viewData=array())
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

        $searchData['ActionUrl']    =  'transaction_list/add';
        $searchData['filterArr']    =  array('from_date', 'to_date', 'users', 'products','buyers', 'status'); 

		//Page Config
		$viewData = array(
		                   'list_title'         => $this->lang->line('title_ap_transaction_list'),
		                   'list_view'          => TRUE,
                           'view_title'  		=> $this->lang->line('request_view_details'),
		            	   'search_title'		=> $this->lang->line('vendor_search_filters'),
		                   'search_view'		=> $this->load->view('common_search_form', $searchData, TRUE)
		                );    
		//Table Config
		$tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
		$this->table->set_template($tmpl); 
    	$this->table->set_heading($this->lang->line('table_head_product_name'), $this->lang->line('table_head_deal_date'), $this->lang->line('label_sellers'), $this->lang->line('label_buyer'),$this->lang->line('table_head_action'));
		$viewData['dataTableUrl']     = 'Transaction_list/datatable';

		$data = array (
		                'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('title_ap_transaction_list'),
		                'content'   =>  $this->load->view('base/form_template', $viewData, TRUE)
		              );
		$this->load->view($this->dbvars->app_template, $data);
	}

	//Take records and view to datatable
	function datatable()
	{
		/* CHECK IF SESSION DATA ARE THERE NOT, IF THERE SET WHERE CONDITION FOR THAT DATA */
		$from_date     = ($this->session->userdata('from_date')) ? $this->session->userdata('from_date') : '-1';
        $to_date       = ($this->session->userdata('to_date')) ? $this->session->userdata('to_date') :  '-1';
        $seller_id     = ($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : '-1';
        $product_id    = ($this->session->userdata('product_id')) ? $this->session->userdata('product_id') : '-1'; 
        $buyer_id      = ($this->session->userdata('buyer_id')) ? $this->session->userdata('buyer_id') : '-1';
        $status_id     = ($this->session->userdata('status_id')) ? $this->session->userdata('status_id') : '1';
     
	    $this->datatables->select('p.product_name, dr.deal_date, u.username, us.username as buyer,dr.deal_request_id')
	                     ->from('deal_request as dr');
	    $this->datatables->join('product as p', 'p.product_id = dr.product_id','left'); 
	    $this->datatables->join('users as u', 'u.user_id = dr.seller_id','left'); 
	    $this->datatables->join('users as us', 'us.user_id = dr.buyer_id','left');  
	    //$this->datatables->where('dr.status', '1'); 		

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
        }

	    $this->datatables->edit_column('dr.deal_date', '$1', 'get_date_timeformat(dr.deal_date)');
        $this->datatables->edit_column('dr.deal_request_id', '$1','get_angular_view(dr.deal_request_id, "Transaction_list/angularViewForm", 1)');

		echo $this->datatables->generate();
	}

	//Load and view form with click on datatable row
	public function angularViewForm($deal_request_id='')
	{
		//TAKEN SELLER ID BASED ON DEAL REQUEST ID
    	$seller_id = $this->mcommon->specific_row_value('deal_request', array('deal_request_id' => $deal_request_id), 'seller_id');
    	
		/*APPROVE AND DISAPPROVE PRODUCTS */
		$formData['productData']    = $this->prefs->getDealtransDetails($deal_request_id);
		$formData['activityData']   = $this->prefs->getactivityList($seller_id);
		echo $this->load->view('angular_forms/new_transaction_form', $formData);
	}
}
?>