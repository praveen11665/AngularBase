<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Prefs extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/********To get the Document list for Document API **********************/
	public function document_record($vendor_type='', $user_id='', $is_mandatory='')
	{
		$this->db->select('d.document_id, d.document_name, d.is_mandatory, d.document_for as vendor_type, GROUP_CONCAT(DISTINCT ar.role_name) as role_name, GROUP_CONCAT(DISTINCT dt.document_type) as documentType, COALESCE(ud.document_path, "") as document_path')
		->from('document as d')
		->join('upload_documents as ud', 'ud.document_id = d.document_id AND ud.user_id = "'.$user_id.'"', 'left')
		->join('app_roles as ar', 'FIND_IN_SET(ar.role_id, d.document_for) != 0', 'left')
		->join('document_type as dt', 'FIND_IN_SET(dt.doc_type_id, d.document_type) != 0', 'left');

		if($vendor_type)
		{
			$this->db->where("FIND_IN_SET('".$vendor_type."', d.document_for)");
		}

		if($is_mandatory)
		{
			$this->db->where('d.is_mandatory', '1');
		}
				
		$this->db->group_by('d.document_id');
		$results= $this->db->get()->result_array();
		$row =array();
		foreach ($results as $row) 
		{
			$document_path = $row['document_path'];
			if($document_path == '')
			{
				$row['document_path'] = '';
				$row['is_upload'] 	  = 'not uploaded';
			}else
			{
				$row['document_path'] = base_url($document_path);				
				$row['is_upload'] 	  = 'uploaded';				
			}
			$data[] = $row;
		}
		return $data;
	}

	/********* TO get Upload documents list **********************************/
	public function get_upload_documents($user_id='')
	{
		$this->db->select('ud.upload_doc_id, d.document_id, d.document_name, ud.user_id, CONCAT(up.first_name, ",", up.last_name)as user_name, ud.document_path, ud.created_on');
		$this->db->from('upload_documents as ud');
		$this->db->join('document as d', 'd.document_id = ud.document_id', 'left');
		$this->db->join('user_profile as up', 'up.user_id = ud.user_id', 'left');

		if($user_id)
		{
			$this->db->where('ud.user_id', $user_id);
		}

		$results= $this->db->get()->result_array();
		$row =array();

		foreach ($results as $row) 
		{
			$imagePath     	= "";
			$pdfPath       	= "";			
			$document_path 	= $row['document_path'];
			
			//Take File extension
			$nameurl    	= explode('.', rtrim($document_path, '.'));
			$fileName   	= array_pop($nameurl);

			if ($fileName == 'jpg' || $fileName == 'jpeg' || $fileName == 'png') 
			{
				$imagePath = base_url($document_path);

			}else if($fileName == 'pdf')
			{
				$pdfPath   = base_url($document_path);
			}

			$row['document_path_image'] = $imagePath;
			$row['document_path_pdf']   = $pdfPath;
			unset($row['document_path']);
			$data[] = $row;
		}
		return $data;
	}	

	/********To get Buyer and seller waiting for approval**********************/
	public function count_wfa($role_id='', $status='0', $from_date='', $to_date='', $user_id='')
	{
		$this->db->select('u.user_id, ud.updated_on')
                 ->from('users as u')
                 ->join('user_profile as up','up.user_id = u.user_id', 'left')
                 ->join('upload_documents as ud','ud.user_id = u.user_id', 'left');
		$whereArray = array('u.auth_level' => $role_id, 'up.profile_status' => '1', 'up.document_status' => '1');
		$this->db->where($whereArray);  

		if($status == '0')
		{
			$this->db->where('up.approved_status', '0');
			$this->db->where('u.banned', '1');
		}else
		{
			$this->db->where('up.approved_status', $status);

			if($status == '2')
			{
				$this->db->where('u.banned', '1');        
			}else
			{
				$this->db->where('u.banned', '0');        
			}
		}

		if ($from_date != '' && $to_date !='') 
	    {
			$this->db->where('DATE(ud.updated_on) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(ud.updated_on) <=', date('Y-m-d', strtotime($to_date)));
	    }

	    if($user_id)
	    {
	    	$this->db->where('u.user_id', $user_id);
	    }

		$this->db->group_by('u.user_id');
		$num_results = $this->db->count_all_results();
		return $num_results;    
	}

	/********To get new deal Counts, Approve, Disapprove Counts**********************/
	public function dealCounts($status='0', $from_date='', $to_date='', $user_id='')
	{
		$this->db->select('p.product_id')
				 ->from('product as p');
		$this->db->where('p.is_delete', '0');

		if($status == '0')
		{
			$this->db->where('p.approved_status', '0');
		}else
		{
			$this->db->where('p.approved_status', $status);
		}

		if ($from_date != '' && $to_date !='') 
	    {
			$this->db->where('DATE(p.updated_on) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(p.updated_on) <=', date('Y-m-d', strtotime($to_date)));
	    }

	    if($user_id)
	    {
	    	$this->db->where('p.user_id', $user_id);
	    }

	    $num_results = $this->db->count_all_results();
		return $num_results; 
	}

	/********To get transaction Counts, Approve, Disapprove Counts**********************/
	public function transactionCounts($status='0', $from_date='', $to_date='', $vendor_type='', $user_id='')
	{
		$this->db->select('dr.deal_request_id')
				 ->from('deal_request as dr');

		if($status == '0')
		{
			$this->db->where('dr.status', '0');
		}else
		{
			$this->db->where('dr.status', $status);
		}

		if ($from_date != '' && $to_date !='') 
	    {
			$this->db->where('DATE(dr.request_date) >=', date('Y-m-d', strtotime($from_date)));
			$this->db->where('DATE(dr.request_date) <=', date('Y-m-d', strtotime($to_date)));
	    }

	    if($vendor_type && $user_id)
	    {
	    	if($vendor_type == '4')
	    	{
	    		$this->db->where('dr.seller_id', $user_id);
	    	}else
	    	{
	    		$this->db->where('dr.buyer_id', $user_id);
	    	}
	    }
	    
	    $num_results = $this->db->count_all_results();
		return $num_results; 
	}

	/**********STRORED ALL PUSH NOTIFICATION AND SEND PASS message_array********/
	public function userNotify($user_id='', $module_id='', $notifyCon=array())
	{
		//Get Device Id for given user_id
		$get_device_id      = $this->mcommon->specific_row_value('user_profile', array('user_id' => $user_id), 'user_device');
		//Get all notification data in to the notification MASTER
		$notificationData 	= $this->mcommon->records_all('notification', array('modules_id' => $module_id));

	    foreach ($notificationData as $row) 
	    {
			$notify_id   = $row->notify_id;
			$title       = strtr($row->title, $notifyCon);
			$content     = strtr($row->content, $notifyCon);
	    }

	    /******************** STORE PUSH NOTIFICATIONS ***********************/
	    $notifyData = array(	    						
								'notify_id'   => $notify_id,
								'user_id'     => $user_id,
								'title'		  => $title,
								'content'	  => $content,
								'view_status' => '0'
	    					);
	    $this->mcommon->common_insert('push_notifications', $notifyData);

	    /******************* SEND PUSH NOTIFY DATA ******************************/
	    $message_array      =   array(
        								'id'  				=>  $get_device_id, 
        							  	'title'   			=>  $title, 
        							  	'body'  			=>  $content,
        							 );
	    $pushResult 		= $this->send_push($message_array);
        return $pushResult;
	}

	/********* CALL CURL TO SEND PUSH NOTIFICATION FOR USERS *******************/
	public function send_push($message_array=array())
    {
        //define('API_ACCESS_KEY', 'AAAA9E_4VHo:APA91bEUi3KqAhyNrlUHQu1moHuoy34WXfo_leVVwA4cbjvU4Xy0k1JgSN1h98JpdfsU-EDA4db6Iiy4JW3EDU6GJ3Pwvc46onE6d8PMFP8FgkBbeIrKmpRPUbCJB62wk_wNfB_G8OX7');
        define('API_ACCESS_KEY', 'AIzaSyDFqYHlV1i_5zqDFeiNjSOIUdiAPLMUDcs');
        
        if(!empty($message_array))
        {
            $registrationIds    = array($message_array['id']);
            $body               = $message_array['body'];
            $title              = $message_array['title'];

            // prep the bundle
            $msg = array
            (
                'body'      => $body,
                'title'     => $title,
                'vibrate'   => 1,
                'sound'     => 1,
            );
            $fields = array
            (
                'registration_ids'  => $registrationIds,
                'notification'      => $msg,
            );
            $headers = array
            (
                'Authorization: key=' . API_ACCESS_KEY,
                'Content-Type: application/json'
            );
             
            $ch = curl_init();
            curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt( $ch,CURLOPT_POST, true );
            curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
            curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
            $result = curl_exec($ch );
            curl_close( $ch );
            return $result;
        }
    }

    /********** GET VENDOR DETAILS(COMPANY, DOCUMENTS) FOR LOAD ANGULAR FORM ****/
    public function getVendorDetails($user_id='')
    {
    	$this->db->select('u.user_id, u.username, ar.role_name, c.company_name, c.address, ci.name as city_name, s.name as state_name, co.country_name, c.cont_first_name, c.cont_email_id, c.cont_number, GROUP_CONCAT(DISTINCT ud.document_path) as document_path, GROUP_CONCAT(DISTINCT doc.document_name) as document_name, dr.reason');
    	$this->db->from('users as u');
    	$this->db->join('app_roles as ar', 'ar.role_id = u.auth_level', 'left');
    	$this->db->join('company as c', 'c.user_id = u.user_id', 'left');
    	$this->db->join('cities as ci', 'ci.city_id = c.city', 'left');
    	$this->db->join('states as s', 's.state_id = c.state', 'left');
    	$this->db->join('countries as co', 'co.country_id = c.country', 'left');
    	$this->db->join('upload_documents as ud', 'ud.user_id = u.user_id', 'left');
    	$this->db->join('document as doc', 'doc.document_id = ud.document_id', 'left');
    	$this->db->join('disapprove_reasons as dr', 'dr.user_id = u.user_id', 'left');

    	if($user_id)
    	{
    		$this->db->where('u.user_id', $user_id);
    	}

    	$this->db->group_by('u.user_id');
    	$results= $this->db->get()->result();
		return $results;
    }

    /********** GET PRODUCT DETAILS FROM PRODUCT TABLE **************************/
    public function getProductDetails($product_id='')
    {
    	$this->db->select('p.product_id, p.product_name, p.tbt_cut_points, p.density, p.viscosity, p.user_agreement_terms, p.user_id, p.updated_on, u.username, ar.role_name, dr.reason');
    	$this->db->from('product as p');
    	$this->db->join('users as u', 'u.user_id = p.user_id', 'left');
    	$this->db->join('app_roles as ar', 'ar.role_id = u.auth_level', 'left');
    	$this->db->join('deals_reasons as dr', 'dr.product_id = p.product_id', 'left');

    	if($product_id)
    	{
    		$this->db->where('p.product_id', $product_id);
    	}

    	$results= $this->db->get()->result();
		return $results;
    }

    /************ GET PRODUCT DETAILS FROM DEAL REQUEST TABLE***********************/
    public function getDealtransDetails($deal_request_id='', $seller_id='', $buyer_id='', $request_date='')
    {
	    $this->db->select('dr.deal_request_id, p.product_name, dr.deal_date, u.username as buyerName, us.username as sellerName, dr.reason, u.user_id, dr.request_date, ar.role_name');
	    $this->db->from('deal_request as dr');
	    $this->db->join('product as p', 'p.product_id = dr.product_id', 'left');
	    $this->db->join('users as u', 'u.user_id = dr.buyer_id', 'left');
	    $this->db->join('users as us', 'us.user_id = dr.seller_id', 'left');
	    $this->db->join('app_roles as ar', 'ar.role_id = us.auth_level', 'left');

	    if($deal_request_id)
	    {
	       $this->db->where('dr.deal_request_id', $deal_request_id);
	    }

	    if($seller_id)
	    {
	       $this->db->where('dr.seller_id', $seller_id);
	    }

	    if($buyer_id)
	    {
	       $this->db->where('dr.buyer_id', $buyer_id);
	    }

	    if($request_date)
	    {
	       $this->db->where('DATE(dr.request_date)', date('Y-m-d', strtotime($request_date)));
	    }

	    $results= $this->db->get()->result();
	    return $results;
    }

    /******************** GET ACTIVITY LIST USER BASED *************************************/
	public function getactivityList($user_id='')
	{
		$this->db->select('al.log_id, al.log_timestamp, al.activity_id, fa.activity_type, fa.activity_icon, fa.activity_class, al.log_activity, al.user_id, u.username');
		$this->db->from('activity_logs as al');
		$this->db->join('fixed_actitivity as fa', 'fa.activity_id = al.activity_id', 'left');
		$this->db->join('users as u', 'u.user_id = al.user_id', 'left');
		$this->db->order_by('al.log_timestamp', 'DESC');

		if($user_id)
		{
			$this->db->where('u.user_id', $user_id);
		}

		$results= $this->db->get()->result();
		return $results;
	}

	/******************* GET TRASCATION DATA FOR LINE CHART ********************************/
	public function getTransactionData()
	{
		$this->db->select('COUNT(dr.deal_request_id)as totalCount, dr.request_date');
		$this->db->from('deal_request as dr');
		$this->db->where('dr.status', '1');
		$this->db->group_by('DATE(dr.request_date)');

		$results 		= $this->db->get()->result();
		return $results;
	}

	/**************** SEARCH PRODUCT DETAILS **************************/
	public function searchProduct($search_name = '', $user_id='', $product_name='', $tbt_cut_points='', $density='', $viscosity='')
	{
		$this->db->select('p.product_id, p.product_name, p.tbt_cut_points, p.density, p.viscosity, p.user_agreement_terms, u.user_id, u.username as sellername, rs.status, p.created_on');
		$this->db->from('product as p');
		$this->db->join('users as u', 'u.user_id = p.user_id', 'left');
		$this->db->join('registration_status as rs', 'rs.reg_id = p.approved_status', 'left');
		$this->db->where('p.is_delete', '0');
		$this->db->where('p.approved_status', '1');

		if($user_id)
		{
			$this->db->where('u.user_id', $user_id);
		}

		if($search_name != '')
		{
			$this->db->group_start();			
			$this->db->or_like('p.product_name', $search_name);			
			$this->db->or_like('p.tbt_cut_points', $search_name);
			$this->db->or_like('p.density', $search_name);		
			$this->db->or_like('p.viscosity', $search_name);		
			$this->db->group_end();			
		}else if($product_name || $tbt_cut_points || $density || $viscosity) 
		{
			$this->db->group_start();

			if($product_name != '')
			{
				$this->db->or_like('p.product_name', $product_name);	
			}

			if($tbt_cut_points != '')
			{
				$this->db->or_like('p.tbt_cut_points', $tbt_cut_points);				
			}

			if($density != '')
			{
				$this->db->or_like('p.density', $density);
			}

			if($viscosity != '')
			{
				$this->db->or_like('p.viscosity', $viscosity);
			}		
			$this->db->group_end();
		}

		$this->db->order_by('p.created_on', 'DESC');


		$results  = $this->db->get()->result();
		return $results;
	}

	/***************** GET CITY DATA ********************************/

	public function getCityData($city_id='')
	{
		$this->db->select('ci.*, s.country_id');
		$this->db->from('cities as ci');
		$this->db->join('states as s', 's.state_id = ci.state_id', 'left');
		if($city_id)
		{
			$this->db->where('ci.city_id', $city_id);
		}
		$result  = $this->db->get()->row();
		return json_encode($result);
	}
}
?>