<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
Class Settings extends Controller
{
	
	function  __construct()
  {
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
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
	
	function index($settings_status='ok')
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
		$data['submitname'] = 'Save API Settings';
		$data['fburl']   = '';
		$data['fbtoken'] = '';
		$data['hrurl']   = '';
		$data['hrtoken'] = '';
		$data['error_data'] = '';
		
		if ($settings_status == 'invalid') {
			$data['error_data'] = $this->session->flashdata('error');;
		}

		//check for settings
		$this->load->model('Settings_model', 'settings');
		$current_settings = $this->settings->get_settings();
		if ($current_settings) {
			$data['submitname'] = 'Update API Settings';
			//set form fields
			$data['fburl']   = $current_settings->fburl;
			$data['fbtoken'] = $current_settings->fbtoken;
			$data['hrurl']   = $current_settings->hrurl;
			$data['hrtoken'] = $current_settings->hrtoken;
		}
		
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		//validation rules
		$this->form_validation->set_rules('fburl', 'FreshBooks API URL', 'required');
		$this->form_validation->set_rules('fbtoken', 'FreshBooks API Token', 'required');
		$this->form_validation->set_rules('hrurl', 'Highrise URL', 'required');
		$this->form_validation->set_rules('hrtoken', 'Highrise API Token', 'required');

		if ($this->form_validation->run() == FALSE){
			$this->load->view('settings/settings_view', $data);
		}else{
			$this->load->model('Settings_model', 'settings');
			if ($_POST['submit']  == 'Update API Settings') {
				$this->settings->update_settings();
			}else{
				$this->settings->insert_settings();
			}
			
			//validate settings
			$params = $this->_get_settings();
			$this->load->library('Highrise_to_freshbooks', $params);
			$check_fb_settings = $this->_validate_api_settings();
			
			$this->load->view('settings/settings_success_view', $data);
		}
	}
}