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
class Document extends REST_Controller 
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
        $this->load->library('upload');
        $this->load->helper('auth');
        $this->load->model('examples/examples_model');
        $this->load->model('examples/validation_callables');
    }

    //Document Upload
    public function document_post()
    {
        $user_id           =   $this->post('user_id');
        $document_id       =   $this->post('document_id');
        $document_path     =   $this->post('document_path');
        $is_image          =   $this->post('is_image');

        if($user_id)
        {
            if($is_image == '1')
            {
                $documentFile             =   'upload_files/documents/'."".time().'.jpg'; // Web Service Image
                $report_image             =   base64_decode($document_path);
                $photo                    =   imagecreatefromstring($report_image);
                imagejpeg($photo, $documentFile, 100);
            }else
            {
                if($_FILES['document_path']['name'] != '')
                {
                    $config = array();
                    /*
                    $config['upload_path']      = 'upload_files/documents';
                    $config['allowed_types']    = '*';
                    $config['max_size']         = '144000';
                    $config['max_width']        = '3500';
                    $config['max_height']       = '3500';
                    $config['max_filename']     = '500';
                    $config['overwrite']        = false;

                    $this->upload->initialize($config);
                    $this->load->library('image_lib');
                    $this->load->library('upload', $config);

                    if($this->upload->do_upload('document_path'))
                    { 
                        $this->load->helper('inflector');
                        $file_name              =   underscore($_FILES['document_path']['name']);
                        $config['file_name']    =   $file_name;
                        $image_data['message']  =   $this->upload->data(); 
                        $documentFile           =   "upload_files/documents/".$image_data['message']['file_name'];
                    } 
                    */

                    $config['upload_path']     = 'upload_files/documents';
                    $config['max_size']        = '1024000000000';
                    $config['allowed_types']   = 'pdf'; # add video extenstion on here
                    $config['overwrite']       = FALSE;
                    $config['remove_spaces']   = TRUE;
                    $documentFile              = time()."_".$_FILES['document_path']['name'];
                    $config['file_name']       = $documentFile;

                    $this->load->library('upload', $config);
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('document_path')) # form input field attribute
                    {
                        # Upload Failed
                        // $data['video']  = $this->upload->display_errors();
                        $documentFile  = '';
                    }
                    else
                    {
                        # Upload Successfull
                        $image_data['message']  =   $this->upload->data(); 
                        $documentFile  = "upload_files/documents/".$image_data['message']['file_name'];;
                    }
                }
            }         

            $result = $this->mcommon->common_insert('upload_documents', array('document_id' => $document_id, 'user_id' => $user_id, 'document_path' => $documentFile, 'created_on' => date('Y-m-d H:i:s')));

            //Edit userProfile page document_status as 1
            $this->mcommon->common_edit('user_profile', array('document_status' => '1'), array('user_id' => $user_id));

            if($result)
            {
                //Log Activity Insert
                $username       = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                $documentname   = $this->mcommon->specific_row_value('document',array('document_id' => $document_id),'document_name');                
                $activityArr    = array(
                                        'log_timestamp'     => date('Y-m-d H:i:s'),
                                        'activity_id'       => 3,
                                        'log_activity'      => $username.",". $this->lang->line('upload_doc')." ".$documentname." ".$this->lang->line('upload_document'),
                                        'log_activity_link' => uri_string(),
                                        'user_id'           => $user_id
                                    );
                $this->mcommon->common_insert('activity_logs', $activityArr); 

                $this->response(['status'=> 'TRUE', 'message' => 'Document Uploaded successfully'], REST_Controller::HTTP_OK); 
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Document Cannot be Uploaded'], REST_Controller::HTTP_NOT_FOUND); 
            }            
        }else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id'], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    //User id basedDocument Edit
    public function document_edit_post()
    {
        $user_id           =   $this->post('user_id');
        $document_id       =   $this->post('document_id');
        $document_path     =   $this->post('document_path');
        $is_image          =   $this->post('is_image');

        if($user_id)
        {
            if($is_image == '1')
            {
                $documentFile             =   'upload_files/documents/'."".time().'.jpg'; // Web Service Image
                $report_image             =   base64_decode($document_path);
                $photo                    =   imagecreatefromstring($report_image);
                imagejpeg($photo, $documentFile, 100);                
            }else
            {
                if($_FILES['document_path']['name'] != '')
                {
                    $config = array();
                    $config['upload_path']      = 'upload_files/documents';
                    $config['allowed_types']    = '*';
                    $config['max_size']         = '144000';
                    $config['max_width']        = '3500';
                    $config['max_height']       = '3500';
                    $config['max_filename']     = '500';
                    $config['overwrite']        = false;
                    $this->upload->initialize($config);
                    $this->load->library('image_lib');
                    $this->load->library('upload', $config);
                    if($this->upload->do_upload('document_path'))
                    { 
                        $this->load->helper('inflector');
                        $file_name              =   underscore($_FILES['document_path']['name']);
                        $config['file_name']    =   $file_name;
                        $image_data['message']  =   $this->upload->data(); 
                        $documentFile           =   "upload_files/documents/".$image_data['message']['file_name'];
                    } 
                }
            } 

            $result = $this->mcommon->common_edit('upload_documents', array('document_path' => $documentFile), array('user_id' => $user_id, 'document_id' => $document_id));

            if($result)
            {
                //Log Activity Insert
                $username       = $this->mcommon->specific_row_value('users',array('user_id' => $user_id),'username');
                $documentname   = $this->mcommon->specific_row_value('document',array('document_id' => $document_id),'document_name');                
                $activityArr    = array(
                                        'log_timestamp'     => date('Y-m-d H:i:s'),
                                        'activity_id'       => 2,
                                        'log_activity'      => $username.",". $this->lang->line('upload_doc_edit')." ".$documentname.$this->lang->line('upload_document'),
                                        'log_activity_link' => uri_string(),
                                        'user_id'           => $user_id
                                    );
                $this->mcommon->common_insert('activity_logs', $activityArr);

                $this->response(['status'=> 'TRUE', 'message' => 'Document Updated successfully'], REST_Controller::HTTP_OK); 
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Document Cannot be Updated'], REST_Controller::HTTP_NOT_FOUND); 
            }            
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please given user_id'], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    //Userid based Document List
    public function document_list_get()
    {
        //Get params
        $user_id    = $this->get('user_id');
        if ($user_id) 
        {
            $result = $this->prefs->get_upload_documents($user_id);
            
            if(!empty($result))
            {
                $this->response($result, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status' => 'FALSE', 'message'=> $this->lang->line('label_documentid_based_response_mesage') ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $this->response(['status' => 'FALSE', 'message'=> $this->lang->line('label_id_response_mesage') ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /*
        Return all missing documts mandatory and not mandatory docs
    */
    public function missing_documents_get()
    {
        $vendor_type = $this->get('vendor_type');
        $user_id     = $this->get('user_id');

        if($vendor_type && $user_id)
        {
            $checkDocs   = $this->prefs->document_record($vendor_type, $user_id);   

            foreach ($checkDocs as $key => $value) 
            {
                if($checkDocs[$key]['document_path'] == '')
                {
                    $no_of_mandatory += COUNT($checkDocs[$key]['document_id']);
                    $docs[]           = $checkDocs[$key]; 
                }
            }

            if(!empty($docs))   
            {
                $this->response($docs, REST_Controller::HTTP_OK);                 
            }else
            {
                $this->response(['status' => 'FALSE', 'message'=> 'No Documents' ], REST_Controller::HTTP_NOT_FOUND);
            }        

        }else
        {
            $this->response(['status' => 'FALSE', 'message'=> 'Please given vendor type and user id' ], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    /*
        Check Document Final step Submit
        If all are documents are submitted
        Check Mandatatory documents submitted or not
        If all documents are submited  step_completed is 1, other wise step_completed 0
    */
    public function check_final_step_post()
    {
        $vendor_type = $this->post('vendor_type');
        $user_id     = $this->post('user_id');

        if($vendor_type && $user_id)
        {
            //Check Disapproved Users as New Users
            $this->mcommon->common_edit('user_profile', array('approved_status' => '0'), array('user_id' => $user_id));

            $checkDocs   = $this->prefs->document_record($vendor_type, $user_id, '1');   

            foreach ($checkDocs as $key => $value) 
            {
                if($checkDocs[$key]['document_path'] == '')
                {
                    $no_of_mandatory += COUNT($checkDocs[$key]['document_id']);
                    $docs[]           = $checkDocs[$key]; 
                }
            }

            $mandatory = ($no_of_mandatory) ? $no_of_mandatory : '0';
            $completed = ($mandatory == '0') ? '1' : '0';

            $result = array(    
                                'step_completed'    => $completed,
                                'no_of_mandatory'   => $mandatory,
                                'document_list'     => $docs
                            );

            $this->response($result, REST_Controller::HTTP_OK); 
        }else
        {
            $this->response(['status' => 'FALSE', 'message'=> 'Please given vendor type and user id' ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
?>