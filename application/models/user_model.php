<?php
Class User_model extends Model {

    public $name   	 = '';
    public $email	 = '';
    public $password = '';

    function __construct()
    {
        // Call the Model constructor
        parent::Model();
    }
    
	function check_for_email($str)
	{
		$this->db->where('email', $str);
		$this->db->from('users');
		$query = $this->db->get();
		return $query->num_rows(); 
	}
    
	function getuser($email)
    {
		$this->db->where('email', $email);
		$this->db->from('users');
		$query = $this->db->get();
		return $query->result();
	}

	function insert_user()
	{
		$this->name   	= $this->input->post('name');
		$this->email 	= $this->input->post('email');
		$this->password = md5($this->input->post('password'));

		$this->db->insert('users', $this);
	}
	
	function get_all_users()
	{
		$query = $this->db->get('users');
		return $query->result();
	}	

}