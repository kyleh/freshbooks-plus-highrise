<?php
Class Oa_settings_model extends Model {

	public $userid = '';
	public $fburl = '';
	public $hrurl = '';
	public $hrtoken = '';
	public $fb_oauth_token = '';
	public $fb_oauth_token_secret = '';


    function __construct()
    {
        // Call the Model constructor
        parent::Model();
    }
    
	function got_settings()
	{
		$this->userid = $this->session->userdata('userid');
		$this->db->where('userid', $this->userid);
		$this->db->from('apisettings');
		$query = $this->db->get();
		return $query->num_rows(); 
	}
    
	 	/**
	 * Gets API settings.
	 *
	 * @return settings object row if records exit, False on no records
	 **/
	function get_settings()
	{
		$this->userid = $this->session->userdata('userid');
		$this->db->where('userid', $this->userid);
		$this->db->from('apisettings');
		$query = $this->db->get();
		if ($query->num_rows > 0) {
			return $query->row();
		}else{
			return FALSE;
		}
	}
	
	function insert_api_settings()
	{
		$this->userid = $this->session->userdata('userid');
		$this->fburl = $this->input->post('fburl');
		$this->hrurl = $this->input->post('hrurl');
		$this->hrtoken = $this->input->post('hrtoken');
	    
		$this->db->insert('apisettings', $this);
	}
	
	function update_api_settings()
	{
		$this->userid = $this->session->userdata('userid');
		$this->fburl = $this->input->post('fburl');
		$this->hrurl  = $this->input->post('hrurl');
		$this->hrtoken = $this->input->post('hrtoken');
		
		$this->db->where('userid',$this->userid);
		$this->db->update('apisettings',$this);
	}	
	
	function insert_oauth_settings($settings)
	{
		$data = array(
			'userid' => $this->session->userdata('userid'),
			'fb_oauth_token' => $settings['fb_oauth_token'],
			'fb_oauth_token_secret' => $settings['fb_oauth_token_secret'],
			);
		
		$this->db->insert('apisettings',$data);
	}
	// 
	function update_oauth_settings($settings)
	{
		$data = array(
			'fb_oauth_token' => $settings['fb_oauth_token'],
			'fb_oauth_token_secret' => $settings['fb_oauth_token_secret']
			);
		
		$this->db->where('userid',$this->session->userdata('userid'));
		$this->db->update('apisettings',$data);
	}
	
	

}