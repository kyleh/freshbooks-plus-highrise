<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
Class Oa_settings extends Controller
{
	
	function  __construct()
  {
		parent::Controller();
		$this->load->helper(array('form', 'url'));
  }
	
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
		$this->load->model('Oa_settings_model','settings');
		$settings = $this->settings->get_settings();
		if ( ! $settings)
		{
			redirect('settings/index');
		}
		else
		{
			return array(
							'fburl' => $settings->fburl,
							'fb_oauth_token_secret' => $settings->fb_oauth_token_secret,
							'fb_oauth_token' => $settings->fb_oauth_token,
							'hrurl' => $settings->hrurl,
							'hrtoken' => $settings->hrtoken,
							);
		}
	}
	
	function _validate_highrise_settings()
	{
		//initialize error container array
		$error_data = array();
		//validate highrise settings
		try {
			$hr_settings_status = $this->highrise_to_freshbooks->validate_highrise_settings();
		} catch (Exception $e) {
			$error_data = array($e->getMessage());
		}
		
		if (!empty($error_data)) 
		{
			$this->load->library('session');
			$this->session->set_flashdata('error', $error_data);
			redirect('oa_settings/index/invalid');
		}
		
		return;
	}
		
	// function _validate_freshbooks_settings()
	// {
	// 	// $fb_settings_status = $this->freshbooks_oauth->validate_freshbooks_settings();
	// 	// $error_data = array();
	// 	// 
	// 	// if (is_string($fb_settings_status))
	// 	// {
	// 	// 	$error_data[] = $fb_settings_status;
	// 	// }
	// 	// if ($error_data) 
	// 	// {
	// 	// 	$this->load->library('session');
	// 	// 	$this->session->set_flashdata('error', $error_data);
	// 	// 	redirect('settings/freshbooks_oauth/invalid');
	// 	// }
	// 	
	// 	
	// 	//TODO: Remove for production
	// 	$settings = $this->_get_settings();
	// 	if ($settings['fb_oauth_token'] == '') {
	// 		$this->load->library('session');
	// 		$this->session->set_flashdata('error', $error_data);
	// 		redirect('settings/freshbooks_oauth');
	// 	}
	// 	
	// 	return;
	// }	
	
	function index($settings_status='ok')
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
		}
		
		$data['title']   = 'Highrise to FreshBooks Sync Tool :: API Settings';
		$data['submitname'] = 'Save API Settings';
		$this->load->model('Oa_settings_model', 'settings');
		
		//setting status invalid returned from TODO: traceback
		if ($settings_status == 'invalid') {
			$data['error_data'] = $this->session->flashdata('error');;
		}

		//check for settings
		$current_settings = $this->settings->get_settings();
		if ($current_settings) {
			$data['submitname'] = 'Update API Settings';
			//set form fields
			$data['fburl']   = $current_settings->fburl;
			$data['hrurl']   = $current_settings->hrurl;
			$data['hrtoken'] = $current_settings->hrtoken;
		}
		
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		//validation rules
		$this->form_validation->set_rules('fburl', 'FreshBooks URL', 'required');
		$this->form_validation->set_rules('hrurl', 'Highrise URL', 'required');
		$this->form_validation->set_rules('hrtoken', 'Highrise API Token', 'required');

		if ($this->form_validation->run() == FALSE){
			$this->load->view('settings/oa_settings_view', $data);
		}else{
			if ($_POST['submit']  == 'Update API Settings') {
				$this->settings->update_api_settings();
			}else{
				$this->settings->insert_api_settings();
			}
			
			//validate highrise settings by pinging highrise api
			$params = $this->_get_settings();
			$this->load->library('Highrise_to_freshbooks', $params);
			
			
			//TODO try catch
			//$check_highrise_settings = $this->_validate_highrise_settings();
			
			//if highrise setting good redirect to freshbooks oauth
			if ($params['fb_oauth_token'] && $params['fb_oauth_token_secret']) {
				redirect('sync/index');
			}
			redirect('oa_settings/freshbooks_oauth');
		}
	}

	// function highrise_settings($settings_status='ok'){
	// 	
	// 	//check for login
	// 	if ($this->_check_login())
	// 	{
	// 		$data['navigation'] = TRUE;	
	// 	}
	// 	
	// 	$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
	// 	$data['submitname'] = 'Save API Settings';
	// 	
	// 	if ($settings_status == 'invalid') {
	// 		$data['error_data'] = $this->session->flashdata('error');;
	// 	}
	// 	
	// 	//check for settings
	// 	$this->load->model('Settings_model', 'settings');
	// 	$current_settings = $this->settings->get_settings();
	// 	if ($current_settings) {
	// 		$data['submitname'] = 'Update API Settings';
	// 		//set form fields
	// 		$data['hrurl']   = $current_settings->hrurl;
	// 		$data['hrtoken'] = $current_settings->hrtoken;
	// 	}
	// 	
	// 	//load form validation helper
	// 	$this->load->library('form_validation');
	// 	$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	// 	//validation rules
	// 	$this->form_validation->set_rules('hrurl', 'Highrise URL', 'required');
	// 	$this->form_validation->set_rules('hrtoken', 'Highrise API Token', 'required');
	// 
	// 	if ($this->form_validation->run() == FALSE){
	// 		$this->load->view('settings/highrise_settings_view', $data);
	// 	}else{
	// 		$this->load->model('Settings_model', 'settings');
	// 		if ($_POST['submit']  == 'Update API Settings') {
	// 			$this->settings->update_highrise_settings();
	// 		}else{
	// 			$this->settings->insert_highrise_settings();
	// 		}
	// 		//validate highrise settings by pinging highrise api
	// 		$params = $this->_get_settings();
	// 		$this->load->library('Highrise_to_freshbooks', $params);
	// 		$check_highrise_settings = $this->_validate_highrise_settings();
	// 		
	// 		//on success goto freshbooks oauth settings page
	// 		$this->load->view('settings/freshbooks_settings_view', $data);
	// 	}
	// 	
	// }

	function freshbooks_oauth($settings_status='ok'){
		//check for login
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
		}
		
		$data['title']   = 'Highrise to Freshbooks Sync Tool :: OAuth Settings';
		
		//load freshbooks oauth library
		$settings = $this->_get_settings();
		$this->load->library('FreshbooksOauth', $settings);
		
		//get request token - set url for authorize - get token response for session vars
		//TODO: try catch
		$auth_data = $this->freshbooksoauth->create_authorize_url();
		
		//DEBUG DATA
		//$data['debug'] = $auth_data;
		//$this->load->view('settings/oa_test_view', $data);
		//return;
		////
		
		$data['auth_url'] = $auth_data['url'];
		//set request token and secret in session vars
		$token_info = array('token' => $auth_data['token'], 'token_secret' => $auth_data['token_secret']);
		$this->session->set_userdata($token_info);
		
		//load authorization url to redirect to freshbooks for authorization
		$this->load->view('settings/freshbooks_oauth_view', $data);
	}

	function request_token_ready(){
	//check for login
	if ($this->_check_login())
	{
		$data['navigation'] = TRUE;
	}
	
	$data['title']  = 'Highrise to Freshbooks Sync Tool :: FreshBooks OAuth Settings Success';
	
	//load freshbooks oauth library
	$settings = $this->_get_settings();
	$this->load->library('FreshbooksOauth', $settings);
	//request access token
	$oauth_settings = array('token' => $this->session->userdata('token'), 'token_secret' => $this->session->userdata('token_secret'));
	//request access token
	$access_token = $this->freshbooksoauth->obtain_access_token($oauth_settings);
	
	
	//Debug Data
	// $data['result'] = $request_access_token;
	// $this->load->view('settings/oauth_success_view', $data);
	// return;
	
	
	//if access request successful add to database
	if ($access_token) {
		$settings['fb_oauth_token'] = $access_token['oauth_token'];
		$settings['fb_oauth_token_secret'] = $access_token['oauth_token_secret'];
		$this->load->model('Oa_settings_model','settings');
		$update_settings = $this->settings->update_oauth_settings($settings);
	}else{
		//TODO: no access tokens redirect to freshbooks_oauth
	}

	$data['result'] = $access_token;
	$this->load->view('settings/oa_success_view', $data);
	
  }

	function oauth_test(){
	
		$data['title']  = 'Highrise to Freshbooks Sync Tool :: FreshBooks API Settings Success';
		$data['navigation'] = TRUE;
		//first time oatuh
		$settings = $this->_get_settings();
		
		$this->load->library('FreshbooksOauth', $settings);
		$url_enc = $this->freshbooksoauth->get_fb_assets(null, null, null);
		
		$data['settings'] = $settings;
		$data['url'] = $url_enc;
		$this->load->view('settings/oauth_test_view', $data);
		
	}

}