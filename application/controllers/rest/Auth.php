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
class Auth extends REST_Controller {

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
        $this->load->model('auth/recovery_model');
    }

    /*
     * In this API to be given username and password
     * To be check a valid user to return userrecords with JSON format
     * _user_confirmed and check_passwd the below functions are to be check users data are TRUE or NOT
     * Anything else throw error
    */
    public function user_get()
    {
        //Get params
        $username       =   $this->get('username');
        $Password       =   $this->get('password');
        //$vendor_type    =   $this->get('vendor_type');
        $requirement    =   '1';

        if (!empty($username) && !empty($Password)) 
        {
            if($auth_data = $this->auth_model->get_auth_data($username))
            {
                //Check the user approve or not 0->new, 1-> Approved, 2-> Disapproved
                $checkApprovedStatus = $this->mcommon->specific_row_value('user_profile', array('user_id' => $auth_data->user_id), 'approved_status');               
                
                if( !$this->_user_confirmed( $auth_data, $requirement, $Password ))
                {
                    $this->response([array('status' => "Incorrect username or password")], REST_Controller::HTTP_NOT_FOUND);
                }
                else if($auth_data->banned == '1') //Banned users only for approved status 0 and  2
                {
                    if($checkApprovedStatus == '2') //Disapproved Users
                    {
                        $this->response([array('status' => "You are disapproved by Lio admin")], REST_Controller::HTTP_NOT_FOUND); 
                    }else
                    {                        
                        $this->response([array('status' => "awaiting approval from Lio admin? Thanks")], REST_Controller::HTTP_NOT_FOUND); 
                    }
                }                
                else
                {
                    //SET LOG FOR VENDOR LOGIN
                    $app_activityArr = array(
                                                'log_timestamp'     => date('Y-m-d H:i:s'),
                                                'activity_id'       => 11,
                                                'log_activity'      => $username." ".$this->lang->line('ven_login'),
                                                'log_activity_link' => uri_string(),
                                                'user_id'           => $auth_data->user_id
                                            );
                    $this->mcommon->common_insert('activity_logs', $app_activityArr);

                    $this->response($this->apimodel->usersInfo($auth_data->user_id), REST_Controller::HTTP_OK);
                }
            }else
            {
                $this->response([array('status' => "User Not Found")], REST_Controller::HTTP_NOT_FOUND); 
            }
        }
        else
        {
            $this->response([array('status' => 'Username and Password not there')], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    //Check the buyer or seller valid roles
    public function checkUser_get($user_id='', $role_id='')
    {
        $roleArr    = explode(",", $role_id);
        $roleData   = $this->apimodel->checkUserRoles($user_id, $role_id);
        
        if(empty($roleData))
        {
            return 0;
        }else
        {
            return 1;
        }
    }   

    private function _user_confirmed( $auth_data, $requirement, $passwd = FALSE )
    {
        // Check if user is banned
        $is_banned = ( $auth_data->banned === '1' );

        // Is this a login attempt
        if( $passwd )
        {
            // Check if the posted password matches the one in the user record
            $wrong_password = ( ! $this->check_passwd( $auth_data->passwd, $passwd ) );
        }

        // Else we are checking login status
        else
        {
            // Password check doesn't apply to a login status check
            $wrong_password = FALSE;
        }

        // Check if the user has the appropriate user level
        $wrong_level = ( is_int( $requirement ) && $auth_data->auth_level < $requirement );

        // Check if the user has the appropriate role
        $wrong_role = ( is_array( $requirement ) && ! in_array( $this->roles[$auth_data->auth_level], $requirement ) );

        // If anything wrong
        if( $wrong_level OR $wrong_role OR $wrong_password )
            return FALSE;

        return TRUE;
    }

    private function check_passwd( $hash, $password )
    {
        if( is_php('5.5') && password_verify( $password, $hash ) ){
            return TRUE;
        }else if( $hash === crypt( $password, $hash ) ){
            return TRUE;
        }

        return FALSE;
    }

    /**
     * If you are using some other way to authenticate a created user,
     * such as Facebook, Twitter, etc., you will simply call the user's
     * record from the database, and pass it to the maintain_state method.
     *
     * So, you must know either the user's username or email address to
     * log them in.
     *
     * How you would safely implement this in your application is your choice.
     * Please keep in mind that such functionality bypasses all of the
     * checks that Community Auth does during a normal login.
     */
    public function social_login($username_or_email_address) {
        // Add the username or email address of the user you want logged in:
        //$username_or_email_address = '';

        if (!empty($username_or_email_address)) {
            $auth_model = $this->authentication->auth_model;

            // Get normal authentication data using username or email address
            if ($auth_data = $this->{$auth_model}->get_auth_data($username_or_email_address)) {
                /**
                 * If redirect param exists, user redirected there.
                 * This is entirely optional, and can be removed if
                 * no redirect is desired.
                 */
                $this->authentication->redirect_after_login();

                // Set auth related session / cookies
                $this->authentication->maintain_state($auth_data);
            }
        } else {
            echo 'Example requires that you set a username or email address.';
        }
    } 

    /*
        1. Get user id, and password change this as encrypted format and update in database
        2. Send response code
    */
    public function change_password_get()
    {
        $user_id       =   $this->get('user_id');
        $password      =   $this->get('password');

        if (!empty($user_id) && !empty($password)) 
        {
            $userData['passwd']    = $this->authentication->hash_passwd($password);
            $whereArray            = array('user_id' => $user_id); 
            $resultUser            = $this->mcommon->common_edit('users', $userData, $whereArray);  

            if($resultUser)
            {
                $this->response(['status'=> 'TRUE', 'message' => 'Your password has been changed successfully'], REST_Controller::HTTP_OK); 
            }else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Cannot update your password'], REST_Controller::HTTP_NOT_FOUND); 
            } 
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id and password'], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /*
        1. get a email id, check that user exist or not
        2. If exist send reset password link to email
        3. Get password and confirm password 
        4. Reset the password in web display success message
    */
    public function recover_password_get()
    {
        $user_string       =   $this->get('email');       
        
        if(isset($user_string))
        {
            $requirement    =   '1';
        
            if($user_string!='')
            {
                // Get user table data if username or email address matches a record
                if($user_data = $this->recovery_model->get_recovery_data($user_string))
                {
                    $recovery_code = substr( $this->authentication->random_salt()
                            . $this->authentication->random_salt()
                            . $this->authentication->random_salt()
                            . $this->authentication->random_salt(), 0, 72 );

                    // Update user record with recovery code and time
                    $this->recovery_model->update_user_raw_data(
                        $user_data->user_id,
                        [
                            'passwd_recovery_code' => $this->authentication->hash_passwd($recovery_code),
                            'passwd_recovery_date' => date('Y-m-d H:i:s')
                        ]
                    );

                    // Set the link protocol
                    $link_protocol = USE_SSL ? 'https' : NULL;

                    // Set URI of link
                    $link_uri = 'auth/recovery_verification/' . $user_data->user_id . '/' . $recovery_code;

                    $body   =   array(
                                        'greeting'      => 'Hello,',
                                        'content'       =>  '<div class="alert alert-success">
                                                            <strong>
                                                                Congratulations, you have created an account recovery link.
                                                            </strong><br>
                                                            You recently made a request to reset your Password. Please click the link below to complete the process. if link is not enabled, please copy and paste it in your browser.
                                                            </p>

                                                            <p>' . anchor(
                                                                site_url( $link_uri, $link_protocol ),
                                                                site_url( $link_uri, $link_protocol ),
                                                                'target ="_blank"'
                                                            ) . '</p>
                                                        </div>
                                                    ',
                                                    'thanks_text'=>'Thanks &amp; Regards, <br />'.$this->dbvars->app_name.'<br />',
                                     );                     

                    $from_email   = 'info@alphasoftz.com';
                    $from_name    = 'loophole';
                    $this->load->library('email');
                    $this->email->from($from_email, ucfirst($from_name));
                    $this->email->to($user_string);
                    $this->email->subject('Password Recovery');
                    $this->email->message($this->load->view('email/transactional_template',$body,TRUE));
                    $this->email->set_mailtype("html");
                    $this->email->send();

                    $this->response(['status'=> 'TRUE', 'message' => 'Please check your Email for Verification'], REST_Controller::HTTP_OK); 
                }
                else
                {
                    $this->response(['status'=> 'FALSE', 'message' => 'Invalid Email, Please check your Email'], REST_Controller::HTTP_NOT_FOUND); 
                }
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Email should not be empty!'], REST_Controller::HTTP_NOT_FOUND); 
            }
        }
        else
        { 
            $this->response(['status'=> 'FALSE', 'message' => 'Invalid Request! This kind of request is not authorized in our server!'], REST_Controller::HTTP_NOT_FOUND);  
        }
    }

    /**
     * Verification of a user by email for recovery
     * 
     * @param  int     the user ID
     * @param  string  the passwd recovery code
     */
    public function recovery_verification( $user_id = '', $recovery_code = '' )
    {
        /// If IP is on hold, display message
        if( $on_hold = $this->authentication->current_hold_status( TRUE ) )
        {
            $view_data['disabled'] = 1;
        }
        else
        {
            // Load resources
            $this->load->model('examples/examples_model');

            if( 
                /**
                 * Make sure that $user_id is a number and less 
                 * than or equal to 10 characters long
                 */
                is_numeric( $user_id ) && strlen( $user_id ) <= 10 &&

                /**
                 * Make sure that $recovery code is exactly 72 characters long
                 */
                strlen( $recovery_code ) == 72 &&

                /**
                 * Try to get a hashed password recovery 
                 * code and user salt for the user.
                 */
                $recovery_data = $this->examples_model->get_recovery_verification_data( $user_id ) )
            {
                /**
                 * Check that the recovery code from the 
                 * email matches the hashed recovery code.
                 */
                if( $recovery_data->passwd_recovery_code == $this->authentication->check_passwd( $recovery_data->passwd_recovery_code, $recovery_code ) )
                {
                    $view_data['user_id']       = $user_id;
                    $view_data['username']     = $recovery_data->username;
                    $view_data['recovery_code'] = $recovery_data->passwd_recovery_code;
                }

                // Link is bad so show message
                else
                {
                    $view_data['recovery_error'] = 1;

                    // Log an error
                    $this->authentication->log_error('');
                }
            }

            // Link is bad so show message
            else
            {
                $view_data['recovery_error'] = 1;

                // Log an error
                $this->authentication->log_error('');
            }

            /**
             * If form submission is attempting to change password 
             */
            if( $this->tokens->match )
            {
                $this->examples_model->recovery_password_change();
            }
        }

        echo $this->load->view('examples/page_header', '', TRUE);

        echo $this->load->view( 'examples/choose_password_form', $view_data, TRUE );

        echo $this->load->view('examples/page_footer', '', TRUE);
    }

    /*
        Change the user device code
    */
    public function user_device_put()
    {
        $user_id    = $this->put('user_id');
        $user_token = $this->put('token');       

        if($user_id !='' && ($user_token !='' || $user_token == '0'))
        {
            $update_array=array('user_device' => $user_token);
            echo $update_user_device = $this->mcommon->common_edit('user_profile',$update_array,array('user_id'=>$user_id));

            if($update_user_device > 0)
            {
                $this->response(['result'=> (string)$update_user_device, 'status'=>'TRUE', 'message'=> 'updated user token'], REST_Controller::HTTP_OK);  
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> 'Unable to update user token'], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $this->response(['status' => 'FALSE', 'message' => 'Please given user id and token'], REST_Controller::HTTP_BAD_REQUEST);    
        }
    }
}
?>