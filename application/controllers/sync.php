<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
Class Sync extends Controller
{
	function __construct()
	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		
		$params = $this->_get_settings();
		$this->load->library('Highrise_to_freshbooks', $params);
		//Debug View True=on False=off
		$this->output->enable_profiler(TRUE);
	}
	
	/**
	 * Private Functions prefixed by _ in CodeIgniter
	 **/
	
	/**
	 * Checks user login status.
	 *
	 * @return bool	True on success, False and redirect to login on fail
	 **/
	function _check_login()
	{
		$loggedin = $this->session->userdata('loggedin');
		if ( ! $loggedin)
		{
			redirect('user/index');
			return FALSE;
		}
		else
		{
			return TRUE;	
		}
	}
	
	/**
	 * Gets API settings from database.
	 *
	 * @return array Array of API settings on success, redirect to settings page on fail
	 **/
	function _get_settings()
	{
		$this->load->model('Settings_model','settings');
		$settings = $this->settings->get_settings();
		if ( ! $settings)
		{
			redirect('settings/index');
		}
		else
		{
			return array(
							'fburl' => $settings->fburl,
							'fbtoken' => $settings->fbtoken,
							'hrurl' => $settings->hrurl,
							'hrtoken' => $settings->hrtoken,
							);
		}
	}

	function _validate_api_settings()
	{
		$fb_settings_status = $this->highrise_to_freshbooks->validate_freshbooks_settings();
		$hr_settings_status = $this->highrise_to_freshbooks->validate_highrise_settings();
		$error_data = array();
		
		if (is_string($fb_settings_status))
		{
			$error_data[] = $fb_settings_status;
		}
		if (is_string($hr_settings_status))
		{
			$error_data[] = $hr_settings_status;
		}
		if ($error_data) 
		{
			$this->load->library('session');
			$this->session->set_flashdata('error', $error_data);
			redirect('settings/index/invalid');
		}
		
		return;
	}
	
	function index()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
			$validate_api_settings = $this->_validate_api_settings();
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: Sync Contacts';
	
		//get Highrise Tags
		$hr_tags = $this->highrise_to_freshbooks->get_highrise_tags();
		
		$data['hr_tags'] = $hr_tags;
		$this->load->view('sync/sync_view', $data);
	}

	function sync_contacts()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: Sync Contacts Results';
		$data['heading'] = 'Sync HighRise Contacts Results';
		$data['result'] = '';
		//$data['name'] = $this->session->userdata('name');
		$data['error'] = '';
		$data['clients'] = '';
		$fb_emails = array();
		
		$settings = $this->_get_settings();
		//TODO: preg replace with proper link
		$data['fb_url'] = $settings['fburl'];
		
		//get highrise clients
		$tag_id = $_POST['tagfilter'];
		$hr_clients = $this->highrise_to_freshbooks->get_highrise_clients($tag_id);
		//exit on api error
		if (preg_match("/Error/", $hr_clients)) {
			$data['error'] = $hr_clients;
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		
		//Generate an array of freshbooks client email addresses for comparison
		//to highrise client email addresses
		//process first FB page
		$fb_clients = $this->highrise_to_freshbooks->get_freshbooks_clients();
		//exit on api error
		if (preg_match("/Error/", $fb_clients)) {
			$data['error'] = $fb_clients;
			$this->load->view('sync/sync_results_view', $data);
			return;
		}
		//no errors on client api request extract email into array
		foreach ($fb_clients->clients->client as $client) {
			$fb_emails[] = (string)$client->email;
		}
		//set pages var based on actual pages returned by FB
		$fb_pages = (integer)$fb_clients->clients->attributes()->pages;
		//process additional FB pages if they exist
		while ($fb_pages > 1) {
			$fb_clients = $this->highrise_to_freshbooks->get_freshbooks_clients($fb_pages);
			//exit on api error
			if (preg_match("/Error/", $fb_clients)) {
				$data['error'] = $fb_clients;
				$this->load->view('sync/sync_results_view', $data);
				return;
			}
		  //no errors on client api request extract email into array
			foreach ($fb_clients->clients->client as $client) {
				$fb_emails[] = (string)$client->email;
			}
			$fb_pages--;
		}//end while
		
		//$data['fb_emails'] = $fb_emails;
		//$data['hr_clients'] = $hr_clients;
		//$this->load->view('sync/sync_results_view', $data);
		
		$sync_results = $this->highrise_to_freshbooks->sync_clients($hr_clients, $fb_emails);
		if (is_string($sync_results)) {
			if (preg_match("/Error/", $sync_results)) {
				$data['error'] = $sync_results;
				$this->load->view('sync/sync_results_view', $data);
				return;
			}
		}
		
		$data['result'] = $sync_results;
		$this->load->view('sync/sync_results_view', $data);
		
	}



}