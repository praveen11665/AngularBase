<?php
/*
 * function that generate the action buttons edit, delete
 * This is just showing the idea you can use it in different view or whatever fits your needs
 */

//This Button for href edit and delete
function get_buttons($id, $url)
{
    $ci 	 = & get_instance();

    $html 	 = '<span class="actions">';
    $html   .= '<a href="' . base_url() .$url.'edit/'.$id .'" title="Edit"><i class="os-icon os-icon-pencil-2"></i></a>';
    $html   .= '&nbsp;&nbsp;';
    $html   .= '<a href="' . base_url().$url.'delete/'.$id .'" title="Delete"><i class="os-icon os-icon-cancel-square"></i></a>';
    $html   .= '</span>';
    return $html;
}

//Only Edit button for Angular View
function only_edit_button($id, $url)
{
    $ci      = & get_instance();
    $html    = '<span class="actions">';
    $html   .= '<a class="editButtonClick" data-form_url="'.base_url().$url."/".trim($id).'" title="Edit"><i class="os-icon os-icon-pencil-2" style="font-size:15px;"></i></a>';
    $html   .= '</span>';
    return $html;
}

//Add New(Popup) Buttons
function get_ajax_buttons($id, $url)
{
    $ci 	 	= & get_instance();
    $formUrl 	= $url.'/ajaxLoadForm';
    $deleteUrl 	= base_url($url.'/delete/'.$id);

    $html 	 = '<span class="actions">';
    $html   .= '<a href="javascript:addNewPop(\''.$formUrl.'\', \''.$id.'\');" title="Edit"><i class="os-icon os-icon-pencil-2"></i></a>';
    $html   .= '&nbsp;&nbsp;';
    $html   .= '<a href="javascript:confirmDelete(\''.$deleteUrl.'\');" href="' . base_url().$url.'delete/'.$id .'" title="Delete"><i class="os-icon os-icon-cancel-square"></i></a>';    
    $html   .= '</span>';
    return $html;
}

function getStatus($status='')
{
	if($status == 1)
	{
		$html = '<span class="badge" style="background-color: green; color: white;" >Active</span>';
	}else
	{
        $html = '<span class="badge" style="background-color: red; color: white;" >In-Active</span>';
	}

	return $html;
}

function get_disable_status($status='')
{
	if($status == 1)
    {
        $html = '<span class="label text-success">Enabled</span>';
    }else
    {
        $html = '<span class="label text-success">Disabled</span>';
    }
	return $html;
}

function get_date_timeformat($date='')
{
	if($date != '0000-00-00 00:00:00')
	{
		return date('d-M-Y', strtotime($date)).' <small>'.date('h:i A', strtotime($date)).'</small>';
	}else
	{
		return '-';
	}
}

function get_user_buttons($id, $url, $banned)
{
    $formUrl    = $url.'ajaxLoadForm';

    $ci 	 = & get_instance();

    $html 	 = '<span class="actions">';
    $html   .= '<a href="' . base_url().$url.'edit/'.$id .'" title="Edit"><i class="os-icon os-icon-pencil-2"></i></a>';
    $html   .= '&nbsp;&nbsp;'; 
    
    if ($banned ==	0) 
    {
        $html   .= '<a href="' . base_url().$url.'statusUpdate/'.$id .'/1" title="Deactivate"><i class="os-icon os-icon-close"></i></a>';
    } 
    else
    {
    	$html   .= '<a href="' . base_url().$url.'statusUpdate/'.$id .'/0" title="Activate"><i class="os-icon os-icon-common-07"></i></a>';	
    } 
    $html   .= '&nbsp;&nbsp;'; 
    $html   .= '<a href="' . base_url().$url.'privilage/'.$id .'" title="Edit Privilege"><i class="os-icon os-icon-user-male-circle2"></i></a>';
    $html   .= '</span>';

    return $html;
}

function get_user_status($bannedStatus='')
{
	if($bannedStatus == 0)
	{
		$html = '<span class="label text-success">Active</span>';
	}else
	{
		$html = '<span class="label text-danger">Banned</span>';
	}

	return $html;
}

function get_image($image_path)
{
    $image_path = base_url().$image_path;
    
    if(getimagesize($image_path))
    {
        $html = '<a href="'.$image_path.'" title="Click image to open " target="_blank"><img src="'.$image_path.'" width="48" height="48"/></a>';
    }else
    {
        $image_path = base_url().'/public/images/No_image.png';
        $html = '<a href="'.$image_path.'" title="No Image Available" target="_blank"><img src="'.$image_path.'" width="48" height="48"/></a>';
    }
    return $html;
}

function get_image_array($image_path='')
{
    //Image path changed as array
    $imageArr = explode(",", $image_path);    

    foreach ($imageArr as $key => $value) 
    {
        $image_path = base_url().trim($value);
        
        if(getimagesize($image_path))
        {
            $html  .= '<a href="'.$image_path.'" title="Click image to open " target="_blank"><img src="'.$image_path.'" width="48" height="48"/></a>';
            $html  .= '&nbsp';
        }else
        {
            $image_path = base_url().'/public/images/No_image.png';
            $html  .= '<a href="'.$image_path.'" title="No Image Available" target="_blank"><img src="'.$image_path.'" width="48" height="48"/></a>';
        }    
    }
    return $html;
}

function getDocumentMandatory($mandatory='')
{
    if($mandatory == 1)
    {
        $html = '<span class="badge" style="background-color : green; color: white;">Yes</span>';
    }else
    {
        $html = '<span class="badge" style="background-color : red; color: white;">No</span>';
    }

    return $html;
}

//to view the document user
function getDocumentFor($document='')
{
    if($document == '4')
    {
        $html = '<span class="label text">Seller</span>';
    }
    else if($document == '5')
    {
        $html = '<span class="label text">Buyer</span>';
    }
    else
    {
        $html = '<span class="label text">Both</span>';
    }     

    return $html;
}

//GET Data given or not
function getReason($reason='')
{
   if($reason)
   {
        return $reason;
   }else
   {
        return "-";
   }
}

//RETURN ALL DATES BETWEEN TWO DATES
function date_range($first, $last, $step = '+1 day', $output_format = 'd-M,y' ) 
{
    $dates      = array();
    $current    = strtotime($first);
    $last       = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

//To Be change role bg_clors for vendors
function getUserRoles($role_name='')
{
    if($role_name == 'Buyer')
    {
        $color = '#f1556c';
    }else if($role_name == 'Seller')
    {
        $color = '#254afc';
    }

    $html = '<span class="badge" style="background-color: '.$color.'; color: white;" >'.$role_name.'</span>';
    return $html;
}

//Vendors Approved Status
function getReportStatus($status='')
{
    if($status == 'New')
    {
        $color = '#254afc';
    }else if($status == 'Approved')
    {
        $color = 'green';
    }else 
    {
         $color = 'red';
    }

    $html = '<span class="badge" style="background-color: '.$color.'; color: white;" >'.$status.'</span>';


    return $html;
}

//Angular View Form
function get_angular_view($id='', $url='', $approve='')
{
    $ci      = & get_instance();
    $iclass  = ($approve) ? 'fa fa-eye iconapprove' : 'os-icon os-icon-pencil-2';
    $title   = ($approve) ? 'View' : 'Edit';
    $html    = '<span class="actions">';    
    $html   .= '<a class="formButtonClick" data-form_url="'.base_url().$url."/".trim($id).'" title="'.$title.'"><i class="'.$iclass.'" style="font-size:15px;"></i></a>';
    $html   .= '</span>';
    return $html;    
}
?>