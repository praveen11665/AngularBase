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
class Transaction extends REST_Controller 
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
    public function transaction_post()
    {
        //Get params
        $product_id     = $this->post('product_id');
        $buyer_id       = $this->post('buyer_id');
        $seller_id      = $this->post('seller_id');   

        if($product_id && $buyer_id && $seller_id)
        {
            $deal_date = $this->mcommon->specific_row_value('product',array('product_id' => $product_id),'updated_on');
            $transactionData = array(
                                        'product_id'       => $product_id,
                                        'buyer_id'         => $buyer_id,
                                        'seller_id'        => $seller_id,
                                        'deal_date'        => $deal_date,
                                        'request_date'     => date('Y-m-d H:i:s'),
                                        'status'           => 0                                 
                                    );
            $result = $this->mcommon->common_insert('deal_request', $transactionData); 

            if($result)
            {
                //Transaction Log Activity Insert
                $username    = $this->mcommon->specific_row_value('users',array('user_id' => $seller_id),'username');
                $buyername   = $this->mcommon->specific_row_value('users',array('user_id' => $buyer_id),'username');
                $productname = $this->mcommon->specific_row_value('product',array('product_id' => $product_id),'product_name');

                $activityArr = array(
                                        'log_timestamp'     => date('Y-m-d H:i:s'),
                                        'activity_id'       => 1,
                                        'log_activity'      => $buyername."-". $this->lang->line('new_deal_request')." ".$productname,
                                        'log_activity_link' => uri_string(),
                                        'user_id'           => $seller_id
                                    );
                $this->mcommon->common_insert('activity_logs', $activityArr);  

                //Send Push Notification with reasons
                $this->prefs->userNotify($seller_id, '5', array('buyer_name' => $buyername, 'username' => $username));

                //Log for Disapproved vendor push notification
                $notifyArr = array(
                                    'log_timestamp'     => date('Y-m-d H:i:s'),
                                    'activity_id'       => 6,
                                    'log_activity'      => $this->lang->line('ven_push_nofify')." ".$username,
                                    'log_activity_link' => uri_string(),
                                    'user_id'           => $seller_id
                                  );
                $this->mcommon->common_insert('activity_logs', $notifyArr);

                $this->response(['status'=> 'TRUE', 'product_id' => (string)$result, 'message' => 'Transaction created successfully'], REST_Controller::HTTP_OK);
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message' => 'Transaction cannot be created'], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Please give a Proper id'], REST_Controller::HTTP_NOT_FOUND); 
        }
    }

    //All Deal Request list
    public function deal_request_list_get()
    {
        $result = $this->mcommon->records_all('deal_request');
        
        if($result)
        {
            $this->response($result, REST_Controller::HTTP_OK);                
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message'=> 'Transactions Not Found'], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    //Active Deal Request
    public function active_deal_request_list_get()
    {
        $result = $this->mcommon->records_all('deal_request', array( 'status' => 1));

        if($result)
        {
            $this->response($result, REST_Controller::HTTP_OK);                
        }
        else
        {
            $this->response(['status'=> 'FALSE', 'message'=> 'Deals Request Not Found'], REST_Controller::HTTP_NOT_FOUND);
        }
    }

    //Search Deal
    public function search_deal_get()
    {
        //Get params
        $seller_id        = $this->get('seller_id');
        $buyer_id         = $this->get('buyer_id');
        $deal_request_id  = $this->get('deal_request_id');
        $request_date     = $this->get('request_date');

        if($seller_id) 
        {
            $sellerTransaction = $this->prefs->getDealtransDetails('', $seller_id);

            if($sellerTransaction)
            {
                $this->response($sellerTransaction, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> 'No List Found in this seller id' ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else if($buyer_id)
        {
            $buyerRequest = $this->prefs->getDealtransDetails('', '', $buyer_id);

            if($buyerRequest)
            {
                $this->response($buyerRequest, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> 'No List Found in this buyer id' ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else if($deal_request_id)
        {
            $transactionList = $this->prefs->getDealtransDetails($deal_request_id);

            if($transactionList)
            {
                $this->response($transactionList, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> 'No List Found in this id' ], REST_Controller::HTTP_NOT_FOUND);
            }
        }
        else if($request_date)
        {
            $transactionList = $this->prefs->getDealtransDetails('', '', '', $request_date);

            if($transactionList)
            {
                $this->response($transactionList, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> 'No List Found in this date' ], REST_Controller::HTTP_NOT_FOUND);
            }
        }        
        else
        {
            $this->response(['status'=>'FALSE', 'message'=> 'ID Not Fount' ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
?>