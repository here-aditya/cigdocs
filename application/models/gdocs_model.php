<?php 

class Gdocs_model extends CI_Model
{  
	private $table;
	
	public function __construct()  
	{  
		// Call the Model constructor  
		parent::__construct();  
		$this->table = 'gdocs';
	}  

	public function getData($sess_id)  
	{  
		//Query the data table for every record and row  
		$this->db->select()
		->where('sess_id', $sess_id);
		$query = $this->db->get($this->table);  

		if ($query->num_rows() <= 0)  
		{  
			return null; 
		}
		else
		{  
			return $query->row();  
		}
	}  
	
	public function setData($sess_id, $gdoc_obj)  
	{ 
		// Save the session data along with gdocs object
		$data = array(
		   'sess_id' => $sess_id ,
		   'gdoc_obj' => $gdoc_obj
		);
		
		if ( $this->db->insert($this->table, $data) )  
		{  
			return true; 
		}
		else
		{  
			return false;  
		}
	} 
	
	
	public function updateData($sess_id, $gdoc_obj)  
	{ 
		// Save the session data along with gdocs object
		$data = array(
		   'gdoc_obj' => $gdoc_obj
		);
		$this->db->where('sess_id', $sess_id);
		
		if ( $this->db->update($this->table, $data) )  
		{  
			return true; 
		}
		else
		{  
			return false;  
		}
	} 
	
	
	public function delSession($cur_session)
	{
		$this->db->delete('sessions', array('session_id' => $cur_session)); 
	}

}  
?>