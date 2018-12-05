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
class Company extends REST_Controller 
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

    /*
        Company Details are separate for 3
        a.Contact person
        b.Company details
        c.Vendor type (buyer or seller)
    */

        //First Step Contact Person
        public function contact_person_post()
        {
            $user_id            =   $this->post('user_id');
            $cont_first_name    =   $this->post('cont_first_name');
            $cont_email_id      =   $this->post('cont_email_id');
            $cont_number        =   $this->post('cont_number');  

            if($user_id)
            {
                $companyData = array(
                                        'user_id'           => $user_id,
                                        'city'              => '0',
                                        'state'             => '0',
                                        'country'           => '0',
                                        'cont_first_name'   => $cont_first_name,
                                        'cont_email_id'     => $cont_email_id,
                                        'cont_number'       => $cont_number,
                                        'created_on'        => date('Y-m-d H:i:s')
                                    );
                $result = $this->mcommon->common_insert('company', $companyData);

                //Edit userProfile page company_id and profile_status as 1
                $this->mcommon->common_edit('user_profile', array('company_id' => $result, 'profile_status' => '1'), array('user_id' => $user_id));

                $this->mcommon->common_edit('users', array('email' => $cont_email_id), array('user_id' => $user_id));

                if(!empty($result))
                {
                    //Log Activity Insert
                    $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                    $activityArr = array(
                                            'log_timestamp'     => date('Y-m-d H:i:s'),
                                            'activity_id'       => 1,
                                            'log_activity'      => $username.",". $this->lang->line('company_contact_details'),
                                            'log_activity_link' => uri_string(),
                                            'user_id'           => $user_id
                                        );
                    $this->mcommon->common_insert('activity_logs', $activityArr);  

                    $this->response(['status'=> 'TRUE', 'company_id' => (string)$result, 'message' => 'Contact Person Details Inserted'], REST_Controller::HTTP_OK);
                }
                else
                {
                    $this->response(['status'=> 'FALSE', 'message' => 'Contact person details cannot be inserted'], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id'], REST_Controller::HTTP_NOT_FOUND); 
            }
        }

        //Contact Person Edit
        public function contact_person_edit_post()
        {
            $user_id            =   $this->post('user_id');
            $company_id         =   $this->post('company_id');
            $cont_first_name    =   $this->post('cont_first_name');
            $cont_email_id      =   $this->post('cont_email_id');
            $cont_number        =   $this->post('cont_number');  

            if($user_id)
            {
                $companyData = array(
                                        'user_id'           => $user_id,
                                        'cont_first_name'   => $cont_first_name,
                                        'cont_email_id'     => $cont_email_id,
                                        'cont_number'       => $cont_number,
                                    );
                $result = $this->mcommon->common_edit('company', $companyData, array('company_id' => $company_id));
                $this->mcommon->common_edit('users', array('email' => $cont_email_id), array('user_id' => $user_id));

                if(!empty($result))
                {
                    //Log Activity Insert
                    $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                    $activityArr = array(
                                            'log_timestamp'     => date('Y-m-d H:i:s'),
                                            'activity_id'       => 1,
                                            'log_activity'      => $username.",". $this->lang->line('company_contact_details'),
                                            'log_activity_link' => uri_string(),
                                            'user_id'           => $user_id
                                        );
                    $this->mcommon->common_insert('activity_logs', $activityArr);  

                    $this->response(['status'=> 'TRUE', 'company_id' => (string)$result, 'message' => 'Contact Person Details Updated'], REST_Controller::HTTP_OK);
                }
                else
                {
                    $this->response(['status'=> 'FALSE', 'message' => 'Contact person details cannot be inserted'], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id'], REST_Controller::HTTP_NOT_FOUND); 
            }
        }

        //Second Step Company details
        public function company_details_post()
        {
            $company_id         =   $this->post('company_id');
            $user_id            =   $this->post('user_id');   
            $company_name       =   $this->post('company_name');
            $address            =   $this->post('address');
            $city               =   $this->post('city');
            $state              =   $this->post('state');
            $country            =   $this->post('country');

            if($company_id)
            {
                $companyData = array(
                                        'company_name'      => $company_name,
                                        'address'           => $address,
                                        'city'              => $city,
                                        'state'             => $state,
                                        'country'           => $country,
                                    );
                $result = $this->mcommon->common_edit('company', $companyData, array('company_id' => $company_id));

                if(!empty($result))
                {
                    //Log Activity Insert
                    $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                    $activityArr = array(
                                            'log_timestamp'     => date('Y-m-d H:i:s'),
                                            'activity_id'       => 1,
                                            'log_activity'      => $username.",". $this->lang->line('company_details_collected'),
                                            'log_activity_link' => uri_string(),
                                            'user_id'           => $user_id
                                        );
                    $this->mcommon->common_insert('activity_logs', $activityArr);  

                    $this->response(['status'=> 'TRUE', 'company_id' => (string)$company_id, 'message' => 'Contact Person Details Inserted'], REST_Controller::HTTP_OK);
                }else
                {
                    $this->response(['status'=>'FALSE', 'message' => 'Company details Cannot be inserted'], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Please given company_id'], REST_Controller::HTTP_NOT_FOUND); 
            }      
        }

        //Third Step Vendor Type updated
        public function vendor_type_post()
        {
            $user_id            =   $this->post('user_id');   
            $vendor_type        =   $this->post('vendor_type');

            if($user_id)
            {
                $result = $this->mcommon->common_edit('users', array('auth_level' => $vendor_type), array('user_id' => $user_id));            

                if(!empty($result))
                {
                    //Log Activity Insert
                    $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id), 'username');
                    $activityArr = array(
                                            'log_timestamp'     => date('Y-m-d H:i:s'),
                                            'activity_id'       => 1,
                                            'log_activity'      => $username.",". $this->lang->line('company_vendor_type'),
                                            'log_activity_link' => uri_string(),
                                            'user_id'           => $user_id
                                        );
                    $this->mcommon->common_insert('activity_logs', $activityArr);  

                    $this->response(['status'=> 'TRUE', 'message' => 'Vendor type updated'], REST_Controller::HTTP_OK);
                }else
                {
                    $this->response(['status'=> 'TRUE', 'message' => 'Vendor type updated'], REST_Controller::HTTP_OK);
                    //$this->response(['status'=>'FALSE', 'message' => 'Vendor type cannot update'], REST_Controller::HTTP_NOT_FOUND);
                }
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id'], REST_Controller::HTTP_NOT_FOUND); 
            }      
        }
    /*
        End of the three functions
    */
    
    //Create a Companey
    public function company_post()
    {
        //Get params
        $user_id            =   $this->post('user_id');   
        $company_name       =   $this->post('company_name');
        $address            =   $this->post('address');
        $city               =   $this->post('city');
        $country            =   $this->post('country');
        $state              =   $this->post('state');
        $cont_first_name    =   $this->post('cont_first_name');
        $cont_email_id      =   $this->post('cont_email_id');
        $cont_number        =   $this->post('cont_number');
        $vendor_type        =   $this->post('vendor_type');      

        if($user_id)
        {
            if($company_logo)
            {
                $companyFile              =   'upload_files/company_logo/'."".time().'.jpg'; // Web Service Image
                $report_image             =   base64_decode($company_logo);
                $photo                    =   imagecreatefromstring($report_image);
                imagejpeg($photo, $companyFile, 100);
            }

            $companyData = array(
                                    'user_id'           => $user_id,
                                    'company_name'      => $company_name,
                                    'address'           => $address,
                                    'city'              => $city,
                                    'state'             => $state,
                                    'country'           => $country,
                                    'cont_first_name'   => $cont_first_name,
                                    'cont_email_id'     => $cont_email_id,
                                    'cont_number'       => $cont_number,
                                    'created_on'        => date('Y-m-d H:i:s')
                                );
            $result = $this->mcommon->common_insert('company', $companyData); 

            //Edit userProfile page company_id and profile_status as 1
            $this->mcommon->common_edit('user_profile', array('company_id' => $result, 'profile_status' => '1'), array('user_id' => $user_id));

            //Change user email as company email and roles
            $this->mcommon->common_edit('users', array('auth_level' => $vendor_type, 'email' => $cont_email_id), array('user_id' => $user_id));

            if(!empty($result))
            {
                //Log Activity Insert
                $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                $activityArr = array(
                                        'log_timestamp'     => date('Y-m-d H:i:s'),
                                        'activity_id'       => 1,
                                        'log_activity'      => $username.",". $this->lang->line('cmpny_created'),
                                        'log_activity_link' => uri_string(),
                                        'user_id'           => $user_id
                                    );
                $this->mcommon->common_insert('activity_logs', $activityArr);  

                $this->response(['status'=> 'TRUE', 'company_id' => (string)$result, 'message' => 'Company inserted successfully'], REST_Controller::HTTP_OK);
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message' => 'Company Cannot be inserted'], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id'], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    //Edit a Companey Details
    public function company_edit_post()
    {
        //Get params
        $user_id            =   $this->post('user_id');   
        $company_name       =   $this->post('company_name');
        $address            =   $this->post('address');
        $city               =   $this->post('city');
        $state              =   $this->post('state');
        $country            =   $this->post('country');
        $cont_first_name    =   $this->post('cont_first_name');
        $cont_email_id      =   $this->post('cont_email_id');
        $cont_number        =   $this->post('cont_number');
        $vendor_type        =   $this->post('vendor_type'); 
        $company_id         =   $this->post('company_id'); 

        if($company_id && $user_id)
        {
            if($company_logo)
            {
                $companyFile              =   'upload_files/company_logo/'."".time().'.jpg'; // Web Service Image
                $report_image             =   base64_decode($company_logo);
                $photo                    =   imagecreatefromstring($report_image);
                imagejpeg($photo, $companyFile, 100);
            }

            $companyData = array(
                                    'user_id'           => $user_id,
                                    'company_name'      => $company_name,
                                    'address'           => $address,
                                    'city'              => $city,
                                    'state'             => $state,
                                    'country'           => $country,
                                    'cont_first_name'   => $cont_first_name,
                                    'cont_email_id'     => $cont_email_id,
                                    'cont_number'       => $cont_number,                                    
                                );

            $result = $this->mcommon->common_edit('company', $companyData, array('company_id' => $company_id)); 

            //Change user email as company email and user roles
            $userResult = $this->mcommon->common_edit('users', array('auth_level' => $vendor_type, 'email' => $cont_email_id), array('user_id' => $user_id));

            if(!empty($result) || !empty($userResult))
            {
                //Log Activity Insert
                $username = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                $activityArr = array(
                                        'log_timestamp'     => date('Y-m-d H:i:s'),
                                        'activity_id'       => 2,
                                        'log_activity'      => $username.",". $this->lang->line('cmpny_updated'),
                                        'log_activity_link' => uri_string(),
                                        'user_id'           => $user_id
                                    );
                $this->mcommon->common_insert('activity_logs', $activityArr); 

                $this->response(['status'=> 'TRUE', 'message' => 'Company updated successfully'], REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Company cannot be updated'], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id and Companey_id'], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    //User id Based Companey list
    public function company_list_get()
    {
        //Get params
        $user_id    = $this->get('user_id');

        if ($user_id) 
        {
            //Check if user is ban or Not
            $bannedUsers = $this->mcommon->specific_row_value('users',array('user_id' => $user_id), 'banned');

            if($bannedUsers == '1')
            {
                $this->response(['status'=> 'FALSE', 'message'=> 'awaiting approval from Lio admin? Thanks'], REST_Controller::HTTP_NOT_FOUND);

            }else
            {
                $result      = $this->apimodel->getCompanyList($user_id);            
                
                if(($result))
                {
                    $this->response($result, REST_Controller::HTTP_OK);                
                }
                else
                {
                    $this->response(['status'=> 'FALSE', 'message'=> 'No Companey found for this id' ], REST_Controller::HTTP_NOT_FOUND);
                }                
            }
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message'=> 'Please given user id' ], REST_Controller::HTTP_NOT_FOUND);            
        }
    }
}
?>