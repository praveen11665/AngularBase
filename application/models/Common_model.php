<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * [record_counts description]
	 * @param  [type] $user_id [users id]
	 * @return [INT]   user's id [description]
	 * @author Ganesh Ananthan
	 */

	public function record_counts($table)
	{
		$this->db->select('*');
		$this->db->from($table);
		$num_results = $this->db->count_all_results();
		return $num_results;
	}

	public function specific_record_counts($table,$constraint_array)
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($constraint_array);
		$num_results = $this->db->count_all_results();
		return $num_results;
	}

	public function specific_record_counts_other($table,$constraint_array)
	{
		$this->db->select('*');
		$this->db->from($table);
		$this->db->where($constraint_array);
		$num_results = $this->db->count_all_results();
		return $num_results;
	}

	public function specific_row_value($table,$constraint_array='',$get_field)
	{
		$this->db->select($get_field);
		$this->db->from($table);
		if(!empty($constraint_array))
		{
			$this->db->where($constraint_array);
		}
		$result= $this->db->get()->row_array();
		return $result[$get_field];
	}

	public function records_all($table, $constraint_array='', $order_by='')
	{
		$this->db->select('*');
		$this->db->from($table);
		if(!empty($constraint_array))
		{
			$this->db->where($constraint_array);
		}
		if(!empty($order_by))
		{
			$this->db->order_by($order_by);
		}
		$results= $this->db->get()->result();
		return $results;
	}

	public function specific_fields_records_all($table, $constraint_array='',$get_field_array='')
	{
		if(!empty($get_field_array))
		{
			$this->db->select($get_field_array);
		}
		else
		{
			$this->db->select('*');
		}
		$this->db->from($table);
		if(!empty($constraint_array))
		{
			$this->db->where($constraint_array);
		}
		$results= $this->db->get()->result_array();
		return $results;
	}

	public function row_records_all($table, $constraint_array='')
	{
		$result = $this->db->get_where($table, $constraint_array);
    	return json_encode($result->row());
	}

	public function json_records_all($table, $constraint_array='')
	{
		$this->db->select('*');
		$this->db->from($table);
		if(!empty($constraint_array))
		{
			$this->db->where($constraint_array);
		}
		$results= $this->db->get()->result();
    	return json_encode($results);
	}

	public function common_insert($table,$data)
	{
	    $this->db->insert($table, $data);
		$result = $this->db->insert_id();
		return $result;
	}

	public function common_edit($table,$data,$where_array)
	{
		$this->db->update($table , $data , $where_array);
		$result = $this->db->affected_rows();
		return $result;
	}

	public function common_delete($table,$where_array)
	{
	   return $this->db->delete($table, $where_array);
	}
	
	public function in_array_rec($needle, $haystack, $strict = false) 
	{
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_rec($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return 0;
	}
	
	public function last_record($table,$pm_key,$date_column)
	{ 
			$query = $this->db->query("SELECT * FROM $table ORDER BY $pm_key DESC LIMIT 1");
			$result = $query->result_array();
				return $result;
	}

	public function common_table_last_updated($table,$pm_key,$date_column)
	{
		$this->db->select($date_column);
		$this->db->from($table);
		$this->db->order_by($pm_key,'desc');
		$this->db->limit('1');
		$result= $this->db->get()->row_array();
		return $this->time_elapsed_string($result[$date_column]);
	}

	public function time_elapsed_string($datetime, $full = false) 
	{
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	function clean_url($string)
	{
	    $url=strtolower($string);
	    $url=str_replace(array("'",'"'), '', $url);
	    $url=str_replace(array(' ','+', '!', '&','-','/','.'), '-', $url);
	    $url=str_replace("?", "", $url);
	    $url=str_replace("---", "-", $url);
	    $url=str_replace("--", "-", $url);
	    return $url;
	}

	public function sendEmailWithTemplate($email_array)
	{
		$this->load->library('email');
		$this->email->set_newline("\r\n");

		$from_email_address	= $this->dbvars->app_email;
		$from_email_name	= $this->dbvars->app_name;
		$to_email_address	= $email_array['to_email'];
		$email_subject		= $email_array['subject'];
		$email_message		= $email_array['message'];

		// Set to, from, message, etc.
		$this->email->from($from_email_address, $from_email_name);
	    $this->email->to($to_email_address);
	    $this->email->subject($email_subject);
	    $this->email->message($email_message);
	    $this->email->send();

		if(isset($email_array['cc']))
		{
			$email_cc=$email_array['cc'];
			$this->email->cc($email_cc);
		}
		if(isset($email_array['bcc']))
		{
			$email_bcc=$email_array['bcc'];
			$this->email->cc($email_bcc);
		}

    	echo $this->email->print_debugger();
		$result = $this->email->send();
	}
  	//  Dropdown Menu Simple
	/**
	* @param $get_field - mention only two params like KEY & VALUE
	- If you want CONCAT two or more fields in the Key OR Value section. pass like that
	- array( CONCAT(user_firstname, '.', user_surname) AS Key, fieldName as Value)
	*/
	public function Dropdown($table, $get_field, $constraint_array='', $groupBy='', $orderby='', $limit='', $optionType='', $joinArr='')
	{

		$this->db->select($get_field);

		$this->db->from($table);
		if(!empty($constraint_array))
		{
			$this->db->where($constraint_array);
		}

		if($groupBy != '')
		{
			$this->db->group_by($groupBy);
		}

		if(!empty($orderby))
		{
			$this->db->order_by($orderby);
		}

		if($limit != '')
		{
			$this->db->limit($limit);
		}
		if(!empty($constraint_array))
		{
			foreach ($joinArr as $tableName => $condition)
			{
			$this->db->join($tableName, $condition, '=');
			}
		}

		$results = $this->db->get()->result();

		$options = array();

		if($optionType == '')
		{
			$options[''] = "-- Select --";
		}
		
		foreach($results as $item)
		{
			$options[$item->Key] = $item->Value;

		}	
		return $options;
	} 

	public function dataUpdate($table, $field, $where, $trans_set)
	{
		$this->db->set("$field", "$field+1", FALSE);
		if($where!='')
		{
			$this->db->where($where);
		}
		if($trans_set!='')
		{
			foreach($trans_set as $row => $val)
			{
				$val_array[] = $val;
				
			}
			$this->db->where_in('naming_series_id', $val_array);
		}
		$this->db->update($table);
		return $result = $this->db->affected_rows();
	}	

	public function join_records_all($fields, $table, $joinArr, $constraint_array = '', $groupBy = '', $orderby='', $limitValue='', $distinct='')
	{
		$this->db->select(implode(',', $fields), FALSE);
		$this->db->from($table);
		foreach ($joinArr as $tableName => $condition)
		{
		$this->db->join($tableName, $condition, 'left');
		}
		if (!empty($constraint_array))
		{
		$this->db->where($constraint_array);
		}

		if(!empty($orderby))
		{
		$this->db->order_by($orderby);
		}

		if($groupBy != '')
		{
		$this->db->group_by($groupBy);
		}

		if($limitValue!='')
		{
		$this->db->limit($limitValue);
		}
		if($distinct!='')
		{
		$this->db->limit($limitValue);
		}

		$results = $this->db->get();
		return $results;
	}	
}
