<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Roles extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load language
		$this->lang->load("setting","english");

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
    if( $this->acl_permits('setting.roles_add') )
    {
      $this->loadForm();
    }
    // Unauthorized access view
    else
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
  public function loadForm($formData = array())
  {
    // Get Data From acl_category tables
    $get_field_array              = array('category_id', 'category_code');
    $formData['categoryData']     = $this->mcommon->records_all('acl_categories', '', $get_field_array);
    // Get Data From acl_actions tables
    $get_field_array              = array('action_id', 'action_code', 'category_id');
    $formData['actionData']       = $this->mcommon->records_all('acl_actions' ,'', $get_field_array);
    //action URL
    $formData['ActionUrl']        = 'modules/roles/formSubmit';

    $formView   = '';
    if($formData['formView'])
    {
      $formView = $this->load->view('modules/roles_form', $formData, TRUE);
    }

    $viewData = array(
                        'form_title'        => ($formData['appRoleData'])?$this->lang->line('label_edit_role'):$this->lang->line('label_create_role'),
                        'list_title'        => $this->lang->line('role_list_title'),
                        'form_view'         => $formView,
                        'view_title'        => $this->lang->line('label_role_select')
                      );

    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_role_name'), lang('label_action'));

    $viewData['dataTableUrl']  = 'modules/Roles/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('role_page_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData,TRUE)
                  );

    $this->load->view($this->dbvars->app_template, $data);
  }

  //To be Submit the form with POST values. The form either Inset or Edit with the codtion
  public function formSubmit($viewData='')
  {
    if(!empty($_POST))
    {	
      //  Validation Rules
      $this->form_validation->set_rules('role_name',$this->lang->line('label_role_name'),'required|callback_alpha_dash_space');     
        
      if($this->form_validation->run() == TRUE)
      {
        if($this->input->post('role_id') == "")
        {
          //Insert
          $field_list = array('role_name' =>  $this->input->post('role_name'));
          $result     = $this->mcommon->common_insert('app_roles', $field_list);

          if($result)
          {
            $actionIdArr      = $this->input->post('action_id');
            $categoryIdArr    = $this->input->post('category_id');

            foreach ($actionIdArr as $key => $val) 
            {
              $field_list = array(
                                  'role_id'       =>  $result,
                                  'action_id'     =>  $actionIdArr[$key],
                                  'category_id'   =>  $categoryIdArr[$key]
                                 );
              $this->mcommon->common_insert('app_roles_actions', $field_list);
            }
            //Session message for added
            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType','success');
            redirect(base_url('modules/roles/add'));
          }
        }
        else
        {
          //Update 
          $field_list     = array('role_name' =>  $this->input->post('role_name'));
          $where          = array('role_id'   => $this->input->post('role_id'));
          $result         = $this->mcommon->common_edit('app_roles', $field_list, $where);

          /******
            Role Action Update -- START --
          *******/

          //Delete existing role actions based on this role_id
       
         $this->mcommon->common_delete('app_roles_actions', $where);
          //Then insert the new role actions for this role_id
          $actionIdArr    = $this->input->post('action_id');
          $categoryIdArr  = $this->input->post('category_id');

          foreach ($actionIdArr as $key => $val) 
          {
            $field_list = array(
                                'role_id'       =>  $this->input->post('role_id'),
                                'action_id'     =>  $actionIdArr[$key],
                                'category_id'   =>  $categoryIdArr[$key]
                               );

            $result = $this->mcommon->common_insert('app_roles_actions', $field_list);
          }
          /****
            Role Action Update -- END --
          *****/
       
         $role_id = $this->input->post('role_id');

          $userArr = $this->mcommon->specific_fields_records_all('users', array('auth_level' =>$role_id), 'user_id');

          foreach ($userArr as $row) 
          {
            $user_id = $row['user_id'];

            //Delete all privillege
            $this->mcommon->common_delete('acl', array('user_id' => $user_id));
            // After clear the previous operation add new one
            /****  Set Roles Action ***/
            //  Get role actions from app_roles_actions table for given roles
            //  Assign actions in acl table for that user_id
          }

          $rolesActions = $this->mcommon->records_all('app_roles_actions', array('role_id' => $role_id));

          //Role based action and users are stored in a acl table.
          foreach ($rolesActions as $role)
          {
            foreach ($userArr as $user) 
            {
              $roleActionValues = array('action_id' => $role->action_id, 'user_id' => $user['user_id']);
              $this->mcommon->common_insert('acl', $roleActionValues);
            }
          }
   
          if($result)
          {
            //Session message for updated
            $this->session->set_flashdata('msg','Updated Successfully');
            $this->session->set_flashdata('alertType','success');
            redirect(base_url('modules/roles/add'));
          }
          else
          {
            //Session message for updated with no change data's 
            $this->session->set_flashdata('msg','No data has been changed');
            $this->session->set_flashdata('alertType','danger');
            redirect(base_url('modules/roles/add'));
          }  
        }
      }
    }
    $this->loadForm();
  }

  //To be Edit the Data
  public function edit($role_id='')
  {
    // acl permission access for edit
    if( $this->acl_permits('setting.roles_edit') )
    {
      $where                          = array('role_id' => $role_id);
      $formData['appRoleData']        = $this->mcommon->records_all('app_roles',$where);
      $formData['appRoleActionData']  = $this->mcommon->records_all('app_roles_actions',$where);
      $formData['formView']           = 'TRUE';
      $this->loadForm($formData);
    }
    // Unauthorized access view
    else
    {
      $view_data='';
      $data = array(
                      'title'     =>  $this->lang->line('unauth_page_title'),
                      'content'   =>  $this->load->view('unauthorized',$view_data,TRUE)
                    );
      $this->load->view('base/error_template', $data);
    }
  }

  //To be Delete the Data
  public function delete($role_id='')
  {
    // acl permission access for delete
    if( $this->acl_permits('setting.roles_delete') )
    {
      $where        = array('role_id' => $role_id);
      $result       = $this->mcommon->common_delete('app_roles',$where);

      if ($result) 
      {
        //Session message for delete
        $this->session->set_flashdata('msg', 'Deleted Successfully');
        $this->session->set_flashdata('alertType','success');
        redirect(base_url('modules/roles/add'));       
      }
    }
    // Unauthorized access view
    else
    {
      $view_data='';
      $data = array(
                      'title'     =>  $this->lang->line('unauth_page_title'),
                      'content'   =>  $this->load->view('unauthorized',$view_data,TRUE)
                    );
      $this->load->view('base/error_template', $data);
    } 
  }

  //Take records and view to datatable
  function datatable()
  {
    $this->datatables->select('role_name, role_id', FALSE);
    $this->datatables->from('app_roles');  
    $this->datatables->where('role_id <', '4');  
    $this->datatables->edit_column('role_id', '$1','get_angular_view(role_id,"modules/roles/angularViewForm")');
    echo $this->datatables->generate();
  }

  //Validation Call back only allow alphabetics with white spaces values other enter throw error
  public function alpha_dash_space($field)
  {
    if (! preg_match('/^[a-zA-Z\s]+$/', $field)) 
    {
        $this->form_validation->set_message('alpha_dash_space', $this->lang->line('alpha_dash_space'));
        return FALSE;
    } else 
    {
        return TRUE;
    }
  }

  //Load edit form with click on datatable row
  public function angularViewForm($role_id='')
  {
    $where                          = array('role_id' => $role_id);
    $formData['appRoleData']        = $this->mcommon->records_all('app_roles',$where);
    $formData['appRoleActionData']  = $this->mcommon->records_all('app_roles_actions',$where);
    // Get Data From acl_category tables
    $get_field_array              = array('category_id', 'category_code');
    $formData['categoryData']     = $this->mcommon->records_all('acl_categories', '', $get_field_array);
    // Get Data From acl_actions tables
    $get_field_array              = array('action_id', 'action_code', 'category_id');
    $formData['actionData']       = $this->mcommon->records_all('acl_actions' ,'', $get_field_array);
    //action URL
    $formData['ActionUrl']        = 'modules/roles/formSubmit';
    echo $this->load->view('modules/roles_form', $formData);
  }
}
?>