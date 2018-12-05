<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Api_model extends CI_Model 
{
	public function __construct()
	{
		parent::__construct();
	}

	//Check userexist with username, if exist return some datas
	public function checkUserExist($username = '')
	{
		$this->db->select('u.user_id, u.banned, up.profile_status, up.document_status, up.approved_status');
		$this->db->from('users as u');
		$this->db->join('user_profile as up', 'up.user_id = u.user_id', 'left');			
		
		if($username != '')
		{		
			$this->db->where('u.username', $username);
		}		
		$result= $this->db->get()->row_array();
		return $result;
	}

	//Return all userdetails(user, userProfile, userAddress, userPhone)
	public function usersInfo($user_id)
	{
		$this->db->select('u.user_id, u.username, CONCAT(up.first_name ," ", up.last_name) as name, u.email, ph.phone_number, ar.role_name, u.banned, u.created_at, u.auth_level as vendor_type, ud.address_line_1, ud.address_line_2, ud.city, ud.state, ud.pincode, up.company_id, up.gender, up.dob, up.avatar, up.job_title, up.cover_image')
		->from('users as u')
		->join('user_phone as ph', 'u.user_id = ph.user_id', 'left')
		->join('app_roles as ar', 'ar.role_id = u.auth_level', 'left')
		->join('user_profile as up', 'u.user_id = up.user_id', 'left')
		->join('user_address as ud', 'u.user_id = ud.user_id', 'left');
		$this->db->where('u.user_id',$user_id);
		$results = $this->db->get()->result();		
		return $results;
	}

	public function checkUserRoles($user_id='', $role_id='')
	{
		$this->db->select('user_role_id');
		$this->db->from('user_roles');
		$this->db->where('user_id', $user_id);
		$this->db->where_in('role_id', $role_id);
		$results = $this->db->get()->result();		
		return $results;	
	}	

	public function getCompanyList($user_id='')
	{
		$this->db->select('c.company_id, c.user_id, c.company_name, CONCAT(c.address, ",", ci.name, ",", s.name, ",", co.country_name)as address, c.cont_first_name, c.cont_number, c.cont_email_id, c.created_on, COUNT(case when(p.approved_status = 1) then p.approved_status end)as no_of_listing, COUNT(case when(p.approved_status = 5) then p.approved_status end)as completed_deals');
		$this->db->from('company as c');
		$this->db->join('countries as co', 'co.country_id = c.country', 'left');
		$this->db->join('states as s', 's.state_id = c.state', 'left');
		$this->db->join('cities as ci', 'ci.city_id = c.city', 'left');
		$this->db->join('product as p', 'p.user_id = c.user_id', 'left');
		$this->db->group_by('c.company_id');

		if($user_id)
		{
			$this->db->where('c.user_id', $user_id);
		}

		$results = $this->db->get()->result();		
		return $results;
	}   
}
?>