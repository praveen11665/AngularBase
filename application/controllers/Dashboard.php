<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		// Force SSL
      	$this->is_logged_in();
		// Form and URL helpers always loaded (just for convenience)
		$this->load->helper('form');
		$this->lang->load("app","english");
		$this->lang->load("dashboard_lang","english");
		$this->load->model('auth/recovery_model');	
	}

	//Load the Dashboard with template
	public function index()
	{
		$this->session->unset_userdata('from_date');
		$this->session->unset_userdata('to_date');

		if(!empty($_POST))
		{
			$searchData = array(
			                      'from_date'     =>   $this->input->post('from_date'),
			                      'to_date'       =>   $this->input->post('to_date'),
			                    );
			$this->session->set_userdata($searchData);
		}
		
        $view_data 	= array(); 
		$data 		= array(
		            	    	'title'     => 	$this->lang->line('dashboard_title'),
		                		'content'   =>	$this->load->view('dashboard',$view_data,TRUE)
		                	);
		$this->load->view($this->dbvars->app_template, $data);
	}

	//Common 404Page error for all modules
	public function common404error($value='')
	{
		$viewData = '';
		$data     = array(
		                  'title'     =>  $this->lang->line('unauth_page_title'),
		                  'content'   =>  $this->load->view('file_not_found_form', $viewData,TRUE)
		                  );
		$this->load->view('base/error_template', $data); 
	}
}