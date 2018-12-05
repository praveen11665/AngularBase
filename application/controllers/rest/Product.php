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
class Product extends REST_Controller 
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

    // Create a Deal
    public function deal_create_post()
    {
    	//Get params
    	$user_id			  = $this->post('user_id');
    	$product_name		  = $this->post('product_name');
    	$tbt_cut_points		  = $this->post('tbt_cut_points');
    	$density			  = $this->post('density');
    	$viscosity			  = $this->post('viscosity');
    	$user_agreement_terms = $this->post('user_agreement_terms');

	    if($user_id)
	    {
    		$productData = array(	
									'user_id'				 =>	$user_id,			    			   
									'product_name'			 => $product_name,
									'tbt_cut_points' 	     => $tbt_cut_points,
									'density'				 => $density,
									'viscosity'				 => $viscosity,
									'user_agreement_terms'	 => $user_agreement_terms,
									'created_on'			 => date('Y-m-d H:i:s')
    		      				);
    		 $result = $this->mcommon->common_insert('product', $productData); 

    		if(!empty($result))
            {
                $this->response(['status'=> 'TRUE', 'product_id' => (string)$result, 'message' => 'Product created successfully'], REST_Controller::HTTP_OK);
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message' => 'Product cannot be created'], REST_Controller::HTTP_NOT_FOUND);
            }
    	}else
        {
            $this->response(['status'=>'FALSE', 'message'=> 'Please given user id' ], REST_Controller::HTTP_NOT_FOUND);            
        }
    }    	

    //Edit a Deal Details
    public function deal_edit_post()
    {
    	//Get params
        $vendor_type          = $this->post('vendor_type');
        $user_id              = $this->post('user_id');
    	$product_id			  = $this->post('product_id');
    	$product_name		  = $this->post('product_name');
    	$tbt_cut_points		  = $this->post('tbt_cut_points');
    	$density			  = $this->post('density');
    	$viscosity			  = $this->post('viscosity');
    	$user_agreement_terms = $this->post('user_agreement_terms');

        if($vendor_type == '')
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Not a valid vendor'], REST_Controller::HTTP_NOT_FOUND); 
        }
        if($vendor_type == '4') //Only for seller to be edit
        {
            if($product_id && $user_id)
            {
                $checkProducts = $this->mcommon->specific_record_counts('product', array('user_id' => $user_id, 'product_id' => $product_id));

                if($checkProducts > 0)
                {
                    if($product_name || $tbt_cut_points || $density || $viscosity || $user_agreement_terms)
                    {
            			$productData = array(
            									'product_name'		   => $product_name,
            									'tbt_cut_points'	   => $tbt_cut_points,
            									'density'			   => $density,
            									'viscosity'			   => $viscosity,
            									'user_agreement_terms' => $user_agreement_terms
            								);

                        $result = $this->mcommon->common_edit('product', $productData, array('product_id' => $product_id)); 

               		 	if(!empty($result))
                        {
                           $this->response(['status'=> 'TRUE', 'message' => 'Product updated successfully'], REST_Controller::HTTP_OK);                
                        }
                        else
                        {
                            $this->response(['status'=> 'FALSE', 'message' => 'Product cannot be updated'], REST_Controller::HTTP_NOT_FOUND);
                        }                        
                    }else
                    {
                        $this->response(['status'=> 'TRUE', 'message' => 'Please Given Data for Edit'], REST_Controller::HTTP_OK);                
                    }                    
                }else
                {
                    $this->response(['status'=> 'TRUE', 'message' => 'This product not for this user id'], REST_Controller::HTTP_OK);
                }
            }
            else
            {
                $this->response(['status'=> 'FALSE', 'message' => 'Please given product id and user id'], REST_Controller::HTTP_NOT_FOUND); 
            }            
        }else if($vendor_type != '4')
        {
            $this->response(['status'=> 'FALSE', 'message' => 'Only Seller can edit the product'], REST_Controller::HTTP_NOT_FOUND); 
        }        
   	}

   	//User id Based Deals list
    /* CONCEPT GET METHOD VENDOR TYPE AND USER ID
    public function deal_list_get()
    {
        //Get params
        $vendor_type   = $this->get('vendor_type');
        $user_id       = $this->get('user_id');
        $product_id    = $this->get('product_id');
 
        if($vendor_type == '4')  //Seller List Product
        {
            if($user_id) 
            {
                $result = $this->prefs->searchProduct('', $user_id);
                
                if(($result))
                {
                    $this->response($result, REST_Controller::HTTP_OK);                
                }
                else
                {
                    $this->response(['status'=>'FALSE', 'message'=> $this->lang->line('label_product_based_response_mesage') ], REST_Controller::HTTP_NOT_FOUND);
                }
            }else
            {
                $this->response(['status'=>'FALSE', 'message'=> 'Not a valid vendor' ], REST_Controller::HTTP_NOT_FOUND);
            }            
        }
        else if($vendor_type == '5') //Buyer see all seller Products
        {
            $result = $this->prefs->searchProduct();
            $this->response($result, REST_Controller::HTTP_OK);
        }
        else if($product_id)
        {
        	$result = $this->mcommon->records_all('product', array('product_id'  =>  $product_id, 'is_delete' => '0', 'approved_status' => '1'));
            
            if($result)
            {
                $this->response($result, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> $this->lang->line('label_product_based_response_mesage') ], REST_Controller::HTTP_NOT_FOUND);
            }
        }else
        {
            $this->response(['status'=>'FALSE', 'message'=> 'Please Given vendor type' ], REST_Controller::HTTP_NOT_FOUND);
        }
    }*/

    public function deal_list_get()
    {
        //Get params
        $product_id     = $this->get('product_id'); 
        $vendor_type    = $this->get('vendor_type');
        $user_id        = '';

        if($vendor_type == 4)
        {
            $user_id        = $this->get('user_id');            
        }

        if($product_id)
        {
            $result = $this->mcommon->records_all('product', array('product_id'  =>  $product_id, 'is_delete' => '0', 'approved_status' => '1'));
            
            if($result)
            {
                $this->response($result, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> $this->lang->line('label_product_based_response_mesage') ], REST_Controller::HTTP_NOT_FOUND);
            }
        }else
        {
            $result = $this->prefs->searchProduct('', $user_id);

            if($result)
            {
                $this->response($result, REST_Controller::HTTP_OK);                
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> $this->lang->line('label_product_found') ], REST_Controller::HTTP_NOT_FOUND);
            }

        }
    }

	//Delete Product
    public function delete_deal_delete()
    {
    	//Parms
    	$product_id    = $this->delete('product_id');  

    	if($product_id)
    	{
        	$result = $this->mcommon->common_edit('product', array('is_delete'  =>  1), array('product_id' => $product_id));

            if($result)
            {
                $this->response(['status'=>'TRUE', 'message'=> 'Product Deleted successfully'], REST_Controller::HTTP_OK);
            }
            else
            {
                $this->response(['status'=>'FALSE', 'message'=> ' Not Deleted'], REST_Controller::HTTP_NOT_FOUND);
            }
    	}
        else
        {
            $this->response(['status'=>'FALSE', 'message'=> 'Please given product id' ], REST_Controller::HTTP_NOT_FOUND);            
        }
    }

    //To be search any products with search name
    /*
        IT WILL BE SEARCH ON SINGLE NAME
    public function search_deal_get()
    {
        $search_name = $this->get('search_name');
        $user_id     = $this->get('user_id');

        if($search_name)
        {
           $searchData = $this->prefs->searchProduct($search_name, $user_id);

           if(!empty($searchData))
           {
                $this->response($searchData, REST_Controller::HTTP_OK);                
           }
           else
           {
                $this->response(['status' => 'FALSE', 'message'=> 'No documents for this search name' ], REST_Controller::HTTP_NOT_FOUND);
           }                       
        }else
        {
            $this->response(['status' => 'FALSE', 'message'=> 'Please given any search names' ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
    */

    public function search_deal_post()
    {
        $product_name   = $this->post('product_name');
        $tbt_cut_points = $this->post('tbt_cut_points');
        $density        = $this->post('density');
        $viscosity      = $this->post('viscosity');
        $user_id        = $this->post('user_id');
        
        $searchData = $this->prefs->searchProduct('', $user_id, $product_name, $tbt_cut_points, $density, $viscosity);

        if(!empty($searchData))
        {
            $this->response($searchData, REST_Controller::HTTP_OK);                
        }
        else
        {
            $this->response(['status' => 'FALSE', 'message'=> 'No Products are there for this search fields' ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
?>