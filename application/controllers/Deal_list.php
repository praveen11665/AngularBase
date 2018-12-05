<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Deal_list extends MY_Controller
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
	    if( $this->acl_permits('vendor.approved_deallist') )
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
        $this->session->unset_userdata('status_id');
        
        if(!empty($_POST))
        {
            $searchData = array(
                                    'from_date'     =>   $this->input->post('from_date'),
                                    'to_date'       =>   $this->input->post('to_date'),
                                    'user_id'       =>   $this->input->post('user_id'),
                                    'product_id'    =>   $this->input->post('product_id'),                        
                                    'status_id'     =>   $this->input->post('status_id')                         
                                );
            $this->session->set_userdata($searchData);
        }

        $searchData['ActionUrl']    =  'deal_list/add';
        $searchData['filterArr']    =  array('from_date', 'to_date', 'users', 'products', 'status'); 

		//Page Config
		$viewData = array(
		                    'list_title'        => $this->lang->line('title_ap_deal_list'),
		                    'list_view'         => TRUE,
		            		'search_title'		=> $this->lang->line('vendor_search_filters'),
                        	'view_title'  		=> $this->lang->line('product_view_details'),
		                    'search_view'		=> $this->load->view('common_search_form', $searchData, TRUE)
		                );    
		//Table Config
		$tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
		$this->table->set_template($tmpl); 
    	$this->table->set_heading(lang('table_head_product_name'), lang('table_head_tbt_cut_points'), lang('label_updated_on'), lang('label_updated_by'), lang('table_head_action'));
		$viewData['dataTableUrl']     = 'Deal_list/datatable';

		$data = array (
		                'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('title_ap_deal_list'),
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
        $user_id       = ($this->session->userdata('user_id')) ? $this->session->userdata('user_id') : '-1';
        $product_id    = ($this->session->userdata('product_id')) ? $this->session->userdata('product_id') : '-1'; 
        $status_id     = ($this->session->userdata('status_id')) ? $this->session->userdata('status_id') : '1'; 
     
	    $this->datatables->select('p.product_name, p.tbt_cut_points, p.updated_on, UCASE(u.username), p.product_id')
	                     ->from('product as p');
	    $this->datatables->join('users as u', 'p.user_id = u.user_id','left');                 
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
        }       

	    $this->datatables->edit_column('p.updated_on', '$1', 'get_date_timeformat(p.updated_on)');
        $this->datatables->edit_column('p.product_id', '$1','get_angular_view(p.product_id, "Deal_list/angularViewForm", 1)');

		echo $this->datatables->generate();
	}

	//Load and view form with click on datatable row
	public function angularViewForm($product_id='')
	{
		/*APPROVE AND DISAPPROVE PRODUCTS */
		$formData['productData']    = $this->prefs->getProductDetails($product_id);
		$user_id 					= $this->mcommon->specific_row_value('product', array('product_id' => $product_id), 'user_id');

		$formData['activityData']   = $this->prefs->getactivityList($user_id);
		echo $this->load->view('angular_forms/product_details_form', $formData);
	}
}
?>