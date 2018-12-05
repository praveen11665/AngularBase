<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Users extends MY_Controller 
{
  public function __construct()
  {
    parent::__construct();        
    // Load language
    $this->lang->load("setting_lang","english");
    // Load Form and Form Validation
    $this->load->helper('form');
    $this->load->library('form_validation');
    // Load resources
    $this->load->helper('auth');
    $this->load->model('examples/examples_model');
    $this->load->model('examples/validation_callables');
    // Check the user is loggedin or not
    $this->is_logged_in();
  }

  //To be check the acl condition and to be allow load form 
  public function add()
  {
    // acl permission access for add
    if( $this->acl_permits('setting.users_add') )
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
  public function loadForm($formData=array())
  {
    //Page config
    $formData['ActionUrl']     = 'modules/Users/formSubmit';  
    $viewData = array(
                        'form_title'        => ($formData['tabledata'])?$this->lang->line('label_users_edit'):$this->lang->line('label_users_create'),
                        'list_title'        => $this->lang->line('label_users_list_title'),
                        'form_view'         => $this->load->view('modules/users_form', $formData, TRUE)
                      );
    //Table config
    $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
    $this->table->set_template($tmpl); 
    $this->table->set_heading(lang('label_username'), lang('label_name'), lang('label_email_address'), lang('label_mobile_number'), lang('label_role_name'), lang('label_status'),lang('label_created_at'),lang('label_action'));

    $viewData['dataTableUrl']  = 'modules/Users/datatable';
    $viewData['message']       = $this->session->flashdata('msg');
    $viewData['alertType']     = $this->session->flashdata('alertType');

    $data = array(
                    'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('user_form_title'),
                    'content'   =>  $this->load->view('base/form_template', $viewData,TRUE)
                  );
    $this->load->view($this->dbvars->app_template, $data);
  }

  //To be Submit the form with POST values. The form either Inset or Edit with the codtion
  public function formSubmit($Data = array())
  {
    if(!empty($_POST))
    { 
      if($this->input->post('user_id') == "")
      {
        //Form Validation      
        $validation_rules[] = array(
            'field' => 'username',
            'label' => 'username',
            'rules' => 'trim|required|max_length[12]|is_unique[' . db_table('user_table') . '.username]',
            'errors' => [
              'is_unique' => 'Username already in use.'
            ]
          );

          $validation_rules[] = array(
            'field'  => 'email',
            'label'  => 'email',
            'rules'  => 'trim|required|valid_email|is_unique[' . db_table('user_table') . '.email]',
            'errors' => [
              'is_unique' => 'Email address already in use.'
            ]
          );

        $this->form_validation->set_rules('email', $this->lang->line('label_email_address'), 'trim|required|valid_email|is_unique[users.email]');
      }

      $validation_rules[] = array(
                                  'field' => 'role_id',
                                  'label' => 'role_id',
                                  'rules' => 'required|integer|in_list[1,6,9,10,11]'
                                );

      if($this->input->post('user_id') == "" || $this->input->post('passwd') != "")
      {
        $validation_rules[] = array(
          'field' => 'passwd',
          'label' => 'passwd',
          'rules' => [
            'trim',
            'required',
            [
              '_check_password_strength',
              [ $this->validation_callables, '_check_password_strength' ]
            ]
          ],
          'errors' => [
            'required' => 'The password field is required.'
          ]
        );
        $this->form_validation->set_rules('confirm_password', lang('label_confirm_password'), 'trim|required|matches[passwd]');
      }

      $this->form_validation->set_rules( $validation_rules );
      $this->form_validation->set_rules('first_name', lang('label_first_name'), 'trim|required');
      $this->form_validation->set_rules('last_name', lang('label_last_name'), 'trim|required');
      $this->form_validation->set_rules('phone_number', lang('label_mobile_number'), 'trim|required');
      $this->form_validation->set_rules('role_id', lang('label_select_role'), 'trim|required');

      if($this->form_validation->run() === TRUE)
      {
        if($this->input->post('user_id') == "")
        {
          //Insert Without Id
          $user_data       = array(
                                    'username'   => $this->input->post('username'),
                                    'email'      => $this->input->post('email'),
                                    'auth_level' => $this->input->post('role_id')
                                  );
          $user_data['passwd']     = $this->authentication->hash_passwd($this->input->post('passwd'));
          $user_data['user_id']    = $this->examples_model->get_unused_id();
          $user_data['created_at'] = date('Y-m-d H:i:s');

          // Insert users login credentials in users table
          $result     = $this->mcommon->common_insert('users', $user_data);

          if( $this->db->affected_rows() == 1 )// IF users created successfully
          {
            // Set Profile Information
            $user_profile = array(
                                  'user_id'           => $user_data['user_id'],
                                  'first_name'        => $this->input->post('first_name'),             
                                  'last_name'         => $this->input->post('last_name'),
                                 );
            // Insert users profile information in users_profile table
            $this->mcommon->common_insert('user_profile', $user_profile);

            $user_contact     = array(
                                      'user_id'         => $user_data['user_id'],
                                      'phone_number'    => $this->input->post('phone_number')
                                    );
            $this->mcommon->common_insert('user_phone', $user_contact);                      

            /****  Set Roles Action 
            //  Get role actions from app_roles_actions table for given roles
            //  Assign actions in acl table for that user_id ******/

            $rolesActions = $this->mcommon->records_all('app_roles_actions', array('role_id' => $this->input->post('role_id')));
            foreach ($rolesActions as $row) 
            {
              $roleActionValues = array('action_id' => $row->action_id, 'user_id' =>$user_data['user_id']);
              $this->mcommon->common_insert('acl', $roleActionValues);              
            }

            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('modules/Users/add'));
          }
        }
        else              
        {
          //To Update The Auth Level Using user_id              
          $user_data       = array(
                                    'auth_level' => $this->input->post('role_id')
                                  );

          if($this->input->post('passwd') != "")
          {
            $user_data['passwd'] = $this->authentication->hash_passwd($this->input->post('passwd'));
          }
          $where_array = array('user_id' => $this->input->post('user_id')); 
          $resultUser     = $this->mcommon->common_edit('users', $user_data, $where_array);
          //To Update user_profile Table Using user_id
          $user_profile = array(      
                                  'first_name'        => $this->input->post('first_name'),             
                                  'last_name'         => $this->input->post('last_name'),
                                );
          $where_array = array('user_id' => $this->input->post('user_id'));              
          $resultProfile      = $this->mcommon->common_edit('user_profile', $user_profile, $where_array);
          //To Update The Contact Number              
          $user_contact = array( 
                                 'phone_number' => $this->input->post('phone_number')
                               );

          $where_array = array('user_id' => $this->input->post('user_id'));
          
          $resultPhone = $this->mcommon->common_edit('user_phone', $user_contact, $where_array);
          
          //Delete all privillege
          $this->mcommon->common_delete('acl', array('user_id' => $this->input->post('user_id')));
          // After clear the previous operation add new one
          /****  Set Roles Action 
          //  Get role actions from app_roles_actions table for given roles
          //  Assign actions in acl table for that user_id *****/

          $rolesActions = $this->mcommon->records_all('app_roles_actions', array('role_id' => $this->input->post('role_id')));

          foreach ($rolesActions as $row) 
          {
            $roleActionValues = array('action_id' => $row->action_id, 'user_id' =>$this->input->post('user_id'));
            $this->mcommon->common_insert('acl', $roleActionValues);              
          }

          if($resultUser || $resultProfile || $resultPhone || $accessResult)
          {
            $this->session->set_flashdata('msg', 'Updated Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('modules/Users/add'));
          }
          else
          {
            $this->session->set_flashdata('msg', 'No data has been changed');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('modules/Users/add'));
          }
        }
      }
    }

    $this->loadForm();
  }

  //To be Edit the Data
  public function edit($user_id='')
  {
    // acl permission access for edit
    if( $this->acl_permits('setting.users_edit') )
    {
      $constraint_array        = array('user_id'   =>   $user_id);
      $formData['tabledata']       = $this->mcommon->records_all('users', $constraint_array);
      $formData['userProfileData'] = $this->mcommon->records_all('user_profile', $constraint_array);
      $formData['userPhoneData']   = $this->mcommon->records_all('user_phone', $constraint_array);
      $this->loadForm($formData);
    }    
    else  // Unauthorized access view
    {
      $view_data  = '';
      $data       = array(
                            'title'     =>  $this->lang->line('unauth_page_title'),
                            'content'   =>  $this->load->view('unauthorized',$view_data,TRUE)
                         );
      $this->load->view('base/error_template', $data);
    }
  }
 
  //Take records and view to datatable
  public function datatable()
  {
    $this->datatables ->select('u.username, CONCAT(up.first_name ," ", up.last_name) as name, u.email, ph.phone_number, ar.role_name, u.banned, u.created_at, u.user_id')
                      ->from('users as u')
                      ->join('user_phone as ph', 'u.user_id = ph.user_id','left')
                      ->join('app_roles as ar', 'ar.role_id = u.auth_level', 'left')
                      ->join('user_profile as up', 'u.user_id = up.user_id','left');               
    $this->datatables ->edit_column('u.user_id', '$1', 'get_user_buttons(u.user_id, "modules/Users/", u.banned)');
    $this->datatables ->edit_column('u.banned', '$1', 'get_user_status(u.banned)');
    $this->datatables ->edit_column('u.created_at', '$1', 'get_date_timeformat(u.created_at)');
    echo $this->datatables->generate();
  }   

  /**
  * privillage Page for this controller
  *
  * The privilage must contain with user_id
  * In that user_id based some privilage are given or remove some privillage.
  * To be prillage that controller functions are accessed by that user.
  **/
  public function privilage($user_id='')
  {
    if( $this->acl_permits('setting.users_privilage'))
    {
      //Page config
      $formData['actionUrl']        = 'modules/Users/formPrivilegeSubmit';
      $constraint_array            = array('user_id' => $user_id);
      $formData['userProfileData'] = $this->mcommon->records_all('user_profile', $constraint_array);

      foreach ($formData['userProfileData'] as $row) 
      {
        $userFullName = $row->first_name." ".$row->last_name;
      }
      $formData['aclData']         = $this->mcommon->records_all('acl', $constraint_array);

      $formData['dataTableUrl']     = 'modules/Users/datatable';
      $formData['actionUrl']        = 'modules/Users/formPrivilegeSubmit';

      //Get Flashdata message
      $formData['message']          = $this->session->flashdata('msg');
      $formData['alertType']        = $this->session->flashdata('alertType');

      //Get Data From acl_category tables
      $get_field_array              = array('category_id', 'category_code');
      $formData['categoryData']     = $this->mcommon->records_all('acl_categories', '', $get_field_array);
      //Get Data From acl_actions tables
      $get_field_array              = array('action_id', 'action_code', 'category_id');
      $formData['actionData']       = $this->mcommon->records_all('acl_actions' ,'', $get_field_array); 

      $viewData = array(
                          'form_title'        => $this->lang->line('role_privilege_heading') ."-". $userFullName,
                          'list_title'        => $this->lang->line('label_users_list_title'),
                          'form_view'         => $this->load->view('modules/privilege_roles_form', $formData, TRUE)
                        );
      //Table config
      $tmpl = array ('table_open'  => '<table id="dataTableId" cellpadding="2" cellspacing="1" class="table table-striped">' );
      $this->table->set_template($tmpl); 
      $this->table->set_heading(lang('label_username'), lang('label_name'), lang('label_email_address'), lang('label_mobile_number'), lang('label_role_name'), lang('label_status'),lang('label_created_at'),lang('label_action'));

      $viewData['dataTableUrl']  = 'modules/Users/datatable';
      $viewData['message']       = $this->session->flashdata('msg');
      $viewData['alertType']     = $this->session->flashdata('alertType');

      $data = array(
                      'title'     =>  $this->dbvars->app_name.' - '.$this->lang->line('user_form_title'),
                      'content'   =>  $this->load->view('base/form_template', $viewData,TRUE)
                    );
      $this->load->view($this->dbvars->app_template, $data);
    }  
    else
    {
      $viewData = '';
      $data     = array(
                          'title'     =>  $this->lang->line('unauth_page_title'),
                          'content'   =>  $this->load->view('unauthorized',$viewData,TRUE)
                       );
      $this->load->view('base/error_template', $data);  
    }
  }

  //Privilage form Subit with Controller with Functions name
  public function formPrivilegeSubmit($value='')
  {
    $this->mcommon->common_delete('acl', array('user_id' => $this->input->post('user_id')));
    // After clear the previous operation add new one
    $rolesActions = $this->input->post('action_id');

    foreach ($rolesActions as $action_id) 
    {
      $roleActionValues = array('action_id' => $action_id, 'user_id' => $this->input->post('user_id'));
      $result = $this->mcommon->common_insert('acl', $roleActionValues);              
    }

    if($result)
    {
        $this->session->set_flashdata('msg', 'Privilege Updated Successfully');
        $this->session->set_flashdata('alertType', 'success');
        redirect(base_url('modules/users/add'));
    }
    else
    {
      $this->session->set_flashdata('msg', 'Opps! No changes has been done');
      $this->session->set_flashdata('alertType', 'danger');
      redirect(base_url('modules/users/add'));
    }            
  }

  /**
  * statusUpdate Page for this controller
  *
  * statusUpdate is working for banned and unbanned users
  **/
  public function statusUpdate($user_id='', $status='')
  {
    //Ban status 0 - Active; 1 - Banned
    $where_array =  array('user_id' => $user_id); 
    $this->mcommon->common_edit('users', array('banned' => $status), $where_array);

    if($status == '1')
    { 
      $this->session->set_flashdata('msg', 'Success! User Has Been Deactivated');
      $this->session->set_flashdata('alertType', 'danger');
      redirect(base_url('modules/users/add'));
    }else
    {
      $this->session->set_flashdata('msg', 'Success! User Has Been Activated');
      $this->session->set_flashdata('alertType', 'success');
      redirect(base_url('modules/users/add'));
    }
  }  

  //Load myProfile Form
  public function myProfile($Data = array())
  {
    $Data['ActionUrl']        =  'modules/Users/profileSubmit';   
    $data = array(
                  'title'     =>  'My Profile Edit',
                  'content'   =>  $this->load->view('modules/user_profile', $Data, TRUE)
                 );
    $this->load->view($this->dbvars->app_template, $data);    
  }

  //Submit Function for myfrofile
  public function profileSubmit($Data = array())
  {
    if(!empty($_POST))
    {
      if($this->input->post('user_id') == "")
      { 
        //Form Validation
        $validation_rules[] = array(
          'field'  => 'email',
          'label'  => 'email',
          'rules'  => 'trim|required|valid_email|is_unique[' . db_table('user_table') . '.email]',
          'errors' => [
            'is_unique' => 'Email address already in use.'
          ]
        );

        $this->form_validation->set_rules('email', lang('label_email_address'), 'trim|required|valid_email|is_unique[users.email]');
      }      

      if($this->input->post('user_id') == "" || $this->input->post('passwd') != "")
      { 
        $this->form_validation->set_rules('passwd', $this->lang->line('label_password'), 'required|callback_is_password_strong');
        $this->form_validation->set_rules('confirm_password', $this->lang->line('label_confirm_password'), 'trim|required|matches[passwd]'); 

        $validation_rules[] = array(
          'field' => 'passwd',
          'label' => 'passwd',
          'rules' => [
            'trim',
            'required',
            [
              '_check_password_strength',
              [ $this->validation_callables, '_check_password_strength']
            ]
          ],
          'errors' => [
            'required' => 'The password field is required.'
          ]
        );
        $this->form_validation->set_rules('confirm_password', lang('label_confirm_password'), 'trim|required|matches[passwd]');
      }

      $this->form_validation->set_rules( $validation_rules );
      $this->form_validation->set_rules('first_name', lang('label_first_name'), 'trim|required|alpha');
      $this->form_validation->set_rules('last_name', lang('label_last_name'), 'trim|required|alpha');
      $this->form_validation->set_rules('phone_number', lang('label_mobile_number'), 'trim|required');
      $this->form_validation->set_rules('phone_number', lang('label_mobile_number'), 'trim|required');

      $this->form_validation->set_rules('address_line_1', lang('label_address_line_1'), 'trim|required');
      $this->form_validation->set_rules('address_line_2', lang('label_address_line_2'), 'trim|required');
      $this->form_validation->set_rules('city', lang('label_city'), 'trim|required');
      $this->form_validation->set_rules('state', lang('label_state'), 'trim|required');
      $this->form_validation->set_rules('country_id', lang('label_country'), 'required');
      $this->form_validation->set_rules('pincode', lang('label_pincode'), 'required');
      
      if($this->form_validation->run() === TRUE)
      {
        if($this->input->post('user_id') == "")
        {
          //Insert Without Id
          $user_data       = array(
                                    'username'   => $this->input->post('username'),
                                    'email'      => $this->input->post('email'),
                                    'auth_level' => $this->input->post('role_id')
                                  );
          $user_data['passwd']     = $this->authentication->hash_passwd($this->input->post('passwd'));
          $user_data['user_id']    = $this->examples_model->get_unused_id();
          $user_data['created_at'] = date('Y-m-d H:i:s');

          // Insert users login credentials in users table
          $result     = $this->mcommon->common_insert('users', $user_data);

          if( $this->db->affected_rows() == 1 )// IF users created successfully
          {
            // Set Profile Information
            $user_profile = array(
                                      'user_id'         => $user_data['user_id'],
                                      'first_name'      => $this->input->post('first_name'),             
                                      'last_name'       => $this->input->post('last_name')
                                    );
            // Insert users profile information in users_profile table
            $this->mcommon->common_insert('user_profile', $user_profile);

            $user_contact     = array(
                                      'user_id'         => $user_data['user_id'],
                                      'phone_number'    => $this->input->post('phone_number')
                                    );
            $this->mcommon->common_insert('user_phone', $user_contact);              


            /****  Set Roles Action ***/
            //  Get role actions from app_roles_actions table for given roles
            //  Assign actions in acl table for that user_id

            $rolesActions = $this->mcommon->records_all('app_roles_actions', array('role_id' => $this->input->post('role_id')));
            foreach ($rolesActions as $row) 
            {
              $roleActionValues = array('action_id' => $row->action_id, 'user_id' =>$user_data['user_id']);
              $this->mcommon->common_insert('acl', $roleActionValues);              
            }

            $this->session->set_flashdata('msg', 'Saved Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('Profile'));
          }
        }
        else              
        {
          $where_array = array('user_id' => $this->input->post('user_id')); 

          if($this->input->post('passwd') != "")
          {
            $user_data['passwd'] = $this->authentication->hash_passwd($this->input->post('passwd'));
            $resultUser          = $this->mcommon->common_edit('users', $user_data, $where_array);
          }
          //To Update user_profile Table Using user_id
          $user_profile = array(      
                                  'first_name' => $this->input->post('first_name'),             
                                  'last_name'  => $this->input->post('last_name')
                                );
          $where_array = array('user_id' => $this->input->post('user_id'));              
          $resultProfile      = $this->mcommon->common_edit('user_profile', $user_profile, $where_array);

          $userExist = $this->mcommon->specific_record_counts('user_address', $where_array);          

          $user_address = array(    
                                  'user_id'           => $this->input->post('user_id'),
                                  'address_line_1'    => $this->input->post('address_line_1'),
                                  'address_line_2'    => $this->input->post('address_line_2'),             
                                  'city'              => $this->input->post('city'),
                                  'state'             => $this->input->post('state'),
                                  'country_id'        => $this->input->post('country_id'),
                                  'pincode'           => $this->input->post('pincode')
                                );
          if($userExist == '0')
          {
            $useraddressresult   = $this->mcommon->common_insert('user_address', $user_address);            
          }
          else
          {
            $useraddressresult   = $this->mcommon->common_edit('user_address', $user_address, $where_array);            
          }

          //To Update The Contact Number              
          $user_contact = array( 
                                 'phone_number' => $this->input->post('phone_number')
                               );

          $where_array = array('user_id' => $this->input->post('user_id'));
          
          $resultPhone = $this->mcommon->common_edit('user_phone', $user_contact, $where_array); 

          if($resultUser || $resultProfile || $resultPhone || $useraddressresult)
          {
            $this->session->set_flashdata('msg', 'Updated Successfully');
            $this->session->set_flashdata('alertType', 'success');
            redirect(base_url('modules/Users/myProfile'));
          }
          else
          {
            $this->session->set_flashdata('msg', 'No data has been changed');
            $this->session->set_flashdata('alertType', 'danger');
            redirect(base_url('modules/Users/myProfile'));
          }
        }
      }
    }
    $this->myProfile();   
  }
}