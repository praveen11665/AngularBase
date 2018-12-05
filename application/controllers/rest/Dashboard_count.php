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
class Dashboard_count extends REST_Controller 
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
    
    //Create a Transaction
    public function dashboard_count_get()
    {
        //Get params
        $vendor_type  = $this->get('vendor_type');
        $user_id      = $this->get('user_id');
        $from_date    = $this->get('from_date');
        $to_date      = $this->get('to_date');

        if($vendor_type)
        {
            //Waiting for approval
            $vendorwfa                 = $this->prefs->count_wfa($vendor_type, '0', $from_date, $to_date, $user_id);
            $approvedVendor            = $this->prefs->count_wfa($vendor_type, '1', $from_date, $to_date, $user_id);
            $disApprovedVendor         = $this->prefs->count_wfa($vendor_type, '2', $from_date, $to_date, $user_id);

            //DEAL
            $newdeals                  = $this->prefs->dealCounts('0', $from_date, $to_date, $user_id);
            $approveddeals             = $this->prefs->dealCounts('1', $from_date, $to_date, $user_id);
            $disApproveddeals          = $this->prefs->dealCounts('2', $from_date, $to_date, $user_id); 
            $overAllDeals              = $newdeals + $approveddeals + $disApproveddeals;

            //TRANSACTION
            $newtransaction            = $this->prefs->transactionCounts('0', $from_date, $to_date, $vendor_type, $user_id);
            $approvedtransaction       = $this->prefs->transactionCounts('1', $from_date, $to_date, $vendor_type, $user_id);
            $disApprovedtransaction    = $this->prefs->transactionCounts('2', $from_date, $to_date, $vendor_type, $user_id); 
            $overAllTransaction        = $newtransaction + $approvedtransaction + $disApprovedtransaction;

            //Douments Count List
            $overallDocuments   = $this->prefs->document_record($vendor_type, $user_id);  
            $uploadedDocuments  = $this->prefs->get_upload_documents($user_id);
            $checkDocs          = $this->prefs->document_record($vendor_type, $user_id);   

            foreach ($checkDocs as $key => $value) 
            {
                if($checkDocs[$key]['document_path'] == '')
                {
                    $missingdocs[]    = $checkDocs[$key]; 
                }
            }

            $countData = array(
                                'Waiting_for_approval'      =>  $vendorwfa,
                                'Approved'                  =>  $approvedVendor,                         
                                'Disapproved'               =>  $disApprovedVendor,
                                'OverallDeals'              =>  $overAllDeals,
                                'New_Deal'                  =>  $newdeals,
                                'Approveddeals'             =>  $approveddeals,
                                'DisApproveddeals'          =>  $disApproveddeals,
                                'OverallTransaction'        =>  $overAllTransaction,
                                'Newtransaction'            =>  $newtransaction,
                                'Approvedtransaction'       =>  $approvedtransaction,
                                'DisApprovedtransaction'    =>  $disApprovedtransaction,
                                'overallDocuments'          =>  count($overallDocuments),
                                'uploadedDocuments'         =>  count($uploadedDocuments),
                                'missingDocuments'          =>  count($missingdocs),
                              );
            $this->response($countData, REST_Controller::HTTP_OK);             
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please give a Proper Vendor Type'], REST_Controller::HTTP_NOT_FOUND); 
        }
    }
}
?>