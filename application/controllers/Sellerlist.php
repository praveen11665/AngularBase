<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Sellerlist extends MY_Controller
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
	    // acl permission access for add
	    if( $this->acl_permits('vendor.approved_seller_list') )
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
                                    'city_id'      	=>   $this->input->post('city_id'),                  
                                    'status_id'     =>   $this->input->post('status_id'),                  
                                );
            $this->session->set_userdata($searchData);
        }

	    //Load Search form with filterArray
	    $searchData['ActionUrl']    =  'sellerlist/add';
        $searchData['filterArr']    =  array('from_date', 'to_date', 'country', 'state', 'status'); 

		$viewData = array(
		            'form_title'        => ($formData['appRoleData'])?$this->lang->line('title_av_seller_list'):$this->lang->line('title_av_seller_list'),
		            'list_title'        => $this->lang->line('title_av_seller_list'),
		            'view_title'        => $this->lang->line('vendor_view_details'),
		            'search_title'		=> $this->lang->line('vendor_search_filters'),
		            'search_view'		=> $this->load->view('common_search_form', $searchData, TRUE)
		          );

		//Table Config
		$tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
		$this->table->set_template($tmpl); 
		$this->table->set_heading(lang('table_head_username'), lang('table_head_company_name'), lang('table_cont_name'),lang('table_head_contact_number'), lang('table_head_registered_on'), lang('table_approved_on'), lang('label_action'));
		$viewData['dataTableUrl']     = 'Sellerlist/datatable';
    	$viewData['view_title']       = lang('vendor_view_details');

		$data = array (
		                'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('title_av_seller_list'),
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
        $country_id    = ($this->session->userdata('country_id')) ? $this->session->userdata('country_id') : '-1';
        $state_id      = ($this->session->userdata('state_id')) ? $this->session->userdata('state_id') : '-1';
        $city_id       = ($this->session->userdata('city_id')) ? $this->session->userdata('city_id') : '-1';
        $status_id     = ($this->session->userdata('status_id')) ? $this->session->userdata('status_id') : '1';  

		$this->datatables->select('u.username, c.company_name, c.cont_first_name, c.cont_number, d.updated_on, up.approved_on, u.user_id')
						 ->from('users as u')
						 ->join('company as c', 'u.user_id = c.user_id', 'left')
						 ->join('upload_documents as d','u.user_id = d.user_id', 'left')                   
						 ->join('user_profile as up','up.user_id = u.user_id', 'left');
		$whereArray = array('u.auth_level' => '4', 'up.profile_status' => '1', 'up.document_status' => '1');
		$this->datatables->where($whereArray);

		if ($from_date != '-1' && $to_date !='-1') 
        {
      		$this->datatables->where('DATE(up.approved_on) >=', date('Y-m-d', strtotime($from_date)));
      		$this->datatables->where('DATE(up.approved_on) <=', date('Y-m-d', strtotime($to_date)));
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
        }

        if($status_id == '1')
	    {
	      $this->datatables->where('u.banned', '0');
	    }
	    else
	    {
	      $this->datatables->where('u.banned', '1');
	    }

		$this->datatables ->edit_column('d.updated_on', '$1', 'get_date_timeformat(d.updated_on)');
		$this->datatables ->edit_column('up.approved_on', '$1', 'get_date_timeformat(up.approved_on)');
		$this->datatables->edit_column('u.user_id', '$1','get_angular_view(u.user_id, "Sellerlist/angularViewForm", 1)');
		$this->datatables->group_by('u.user_id');
		echo $this->datatables->generate();
	}

	//Load edit form with click on datatable row
	public function angularViewForm($user_id='')
	{
    	/*USER DATA AND ACTIVITY DATA ARE GIVEN TO THE FORM*/
		$formData['userData']       = $this->prefs->getVendorDetails($user_id);
    	$formData['activityData']   = $this->prefs->getactivityList($user_id);
		echo $this->load->view('angular_forms/vendor_detail_form', $formData);
	}
}
?>