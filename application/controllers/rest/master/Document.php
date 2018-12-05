<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
require APPPATH . 'libraries/REST_Controller.php';

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
class Document extends REST_Controller {

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
        $this->load->helper('form');
    }

    public function document_get()
    {
        //Get params
        $vendor_type    = $this->get('vendor_type');
        $user_id        = $this->get('user_id');
        
        if (!empty($vendor_type) && !empty($user_id)) 
        {
            //Retrive Document List
            $result = $this->prefs->document_record($vendor_type, $user_id);   

            if(!empty($result))
            {
                $this->response($result, REST_Controller::HTTP_OK);                
            }else
            {
                $this->response(['status'=>'FALSE', 'message'=> $this->lang->line('label_documentid_based_response_mesage') ], REST_Controller::HTTP_NOT_FOUND);
            }           
        }      
        else
        {
            $this->response(['status'=>'FALSE', 'message'=> $this->lang->line('given_vendor_id_and_user_id') ], REST_Controller::HTTP_NOT_FOUND);
        }        
    }
}
?>