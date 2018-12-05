<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';
//include APPPATH . 'third_party/community_auth/library/Authentication.php';
/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Registration extends REST_Controller 
{
    function __construct()
    {
        // Construct the parent class
        parent::__construct();

        // Configure limits on our controller methods
        // Ensure you have created the 'limits' table and enabled 'limits' within application/config/rest.php
        $this->methods['users_get']['limit'] = 500; // 500 requests per hour per user/key
        $this->methods['users_post']['limit'] = 100; // 100 requests per hour per user/key
        $this->methods['users_delete']['limit'] = 50; // 50 requests per hour per user/key
        $this->load->helper('url');
        //$this->load->helper('auth');
        $this->load->helper('form');
        $this->load->model('auth_model');
        // Load resources
        $this->load->helper('auth');
        $this->load->model('examples/examples_model');
        $this->load->model('examples/validation_callables');
    }

    public function registration_post()
    {
        //Get params
        $username       =   $this->post('username');
        $password       =   $this->post('password');

        if (!empty($username) && !empty($password)) 
        {
            //Check Whether the user exist or not
            $userData = $this->apimodel->checkUserExist($username);            

            if($userData['user_id'] == '')
            {
                //Insert Without Id
                $user_data  = array(
                                        'username'   => $username,
                                        'banned'     => '1',
                                        'auth_level' => '3'
                                    );

                $user_data['passwd']     = $this->authentication->hash_passwd($password);
                $user_data['user_id']    = $this->examples_model->get_unused_id();
                $user_data['created_at'] = date('Y-m-d H:i:s');

                // Insert users login credentials in users table
                $result     = $this->mcommon->common_insert('users', $user_data);

                if( $this->db->affected_rows() == 1 )// IF users created successfully
                {
                    // Set Profile Information
                    $user_profile = array(
                                          'user_id'           => $user_data['user_id'],
                                          'first_name'        => $username,             
                                         );
                    // Insert users profile information in users_profile table
                    $this->mcommon->common_insert('user_profile', $user_profile);

                    $user_contact     = array(
                                              'user_id'         => $user_data['user_id'],
                                            );
                    $this->mcommon->common_insert('user_phone', $user_contact);                      

                    /****  Set Roles Action ***/
                    //  Get role actions from app_roles_actions table for given roles
                    //  Assign actions in acl table for that user_id

                    $rolesActions = $this->mcommon->records_all('app_roles_actions', array('role_id' => '3'));
                    foreach ($rolesActions as $row) 
                    {
                      $roleActionValues = array('action_id' => $row->action_id, 'user_id' =>$user_data['user_id']);
                      $this->mcommon->common_insert('acl', $roleActionValues);              
                    }

                    //Log Activity Insert
                    $activityArr = array(
                                            'log_timestamp'     => date('Y-m-d H:i:s'),
                                            'activity_id'       => 5,
                                            'log_activity'      => $username.",". $this->lang->line('register_suc'),
                                            'log_activity_link' => uri_string(),
                                            'user_id'           => $user_data['user_id']
                                        );
                    $this->mcommon->common_insert('activity_logs', $activityArr);
                }

                $this->response([array('status' => 'TRUE', 'user_id' => (string)$user_data['user_id'], 'message' => 'Registration Successfully')], REST_Controller::HTTP_OK); 
            }
            else
            {
                if($userData['profile_status'] == '1' && $userData['document_status'] == '1' && $userData['approved_status'] == '0')
                {
                    $this->response([array('status' => 'FALSE', 'message' => 'Please Wait, Admin Cannot Approve')], REST_Controller::HTTP_NOT_FOUND); 
                }
                else if($userData['banned'] == '1')
                {
                    //To be Edit the users      
                    $user_data['username']  = $username;
                    $user_data['passwd']    = $this->authentication->hash_passwd($password);
                    $where_array            = array('user_id' => $userData['user_id']); 
                    $resultUser             = $this->mcommon->common_edit('users', $user_data, $where_array);          
              
                    //Delete all privillege
                    $this->mcommon->common_delete('acl', $where_array);
                    // After clear the previous operation add new one

                    //  Get role actions from app_roles_actions table for given roles
                    //  Assign actions in acl table for that user_id

                    $rolesActions = $this->mcommon->records_all('app_roles_actions', array('role_id' => '3'));

                    foreach ($rolesActions as $row) 
                    {
                        $roleActionValues = array('action_id' => $row->action_id, 'user_id' => $userData['user_id']);
                        $this->mcommon->common_insert('acl', $roleActionValues);              
                    }

                    if($resultUser)
                    {
                        $this->response([array('status' => 'TRUE', 'user_id' => (string)$userData['user_id'], 'message' => 'Registration Successfully')], REST_Controller::HTTP_OK); 
                    }                    
                }
                else
                {                    
                    $this->response([array('status' => 'FALSE', 'message' => 'Username already exist')], REST_Controller::HTTP_NOT_FOUND); 
                }
            }
        }
        else
        {
            $this->response([array('status' => 'FALSE', 'message' => 'Plese give username or password')], REST_Controller::HTTP_NOT_FOUND); 
        }
    }
}
?>