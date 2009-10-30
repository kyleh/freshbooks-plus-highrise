<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*Settings Controller
*Controller to add/update FreshBooks settings and Highrise settings
*Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
*Ver. 1.0 October 2009
*
*Copyright (c) 2009, Kyle Hendricks - Mend Technologies
*All rights reserved.
*Redistribution and use in source and binary forms, with or without
*modification, are permitted provided that the following conditions are met:
** Redistributions of source code must retain the above copyright notice,
*this list of conditions and the following disclaimer.
** Redistributions in binary form must reproduce the above copyright
*notice, this list of conditions and the following disclaimer in the
*documentation and/or other materials provided with the distribution.
** Neither the name of the <ORGANIZATION> nor the names of its
*contributors may be used to endorse or promote products derived from this
*software without specific prior written permission.
*THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
*ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
*WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
*DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
*ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
*(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
*LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
*ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
*(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
*SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
**/
Class Settings extends Controller
{
	
	function  __construct()
	 {
		parent::Controller();
		$this->load->helper(array('form', 'url'));
	 }
	
	/**
	 * Checks for FreshBooks and Highrise Settings
	 * Validates FreshBooks and Highrise Settings
	 *
	 * @return on FreshBooks settings fail/not present redirects to freshbooks oauth process
	 * @return on FreshBooks server down for maintenance redirects to oauth error view with message
	 * @return on Highrise settings not present redirects to main settings page
	 * @return on Highrise settings not valid redirects to main settings page with error
	 *
	 **/
	public function index()
	{
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;
		}
		
		//get current settings
		$settings = $this->_get_settings();
		//check for freshbooks oauth settings
		if ($settings['fb_settings']) {
			//validate freshbooks settings
			$validate = $this->_validate_fb_settings($settings);
			if ($validate != 'valid') {return;}
		}else{
			//if no settings start oauth process
			redirect('settings/freshbooks_oauth');
			return;
		}
		
		//check for highrise settings
		if ($settings['hr_settings']) {
			//validate highrise settings
			$validate = $this->_validate_hr_settings($settings);
			if ($validate != 'valid') {return;}
		}else{
			//if no settings load settings page
			$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
			$data['submitname'] = 'Save API Settings';
			$this->load->view('settings/settings_view', $data); 
			return;
		}
		
		$data['title']   = 'Highrise to FreshBooks Sync Tool :: API Settings';
		$data['submitname'] = 'Update API Settings';
		$raw_domain = $settings['hrurl'];
		$data['hrurl'] = preg_replace('%http[a-z]*://|\.[a-zA-Z0-9]*\.com%', '', $raw_domain);
		$data['hrtoken'] = $settings['hrtoken'];
		$this->load->view('settings/settings_view', $data); 
		return;
	}
	
	/**
	 * Public highrise_settings() method of Settings Controller
	 *
	 * Validates highrise settings input via Post.
	 * Adds Highrise settings to database
	 * Validates Highrise settings
	 *
	 * @param  array|string  $_POST['hrurl]  Highrise subdomain
	 * @param  array|string  $_POST['hrtoken']  Highrise token
	 *
	*/
	public function highrise_settings()
	{
		//check for login
		if ($this->_check_login())
		{
			$data['navigation'] = TRUE;	
		}
		$data['title']   = 'Highrise to FreshBooks Sync Tool :: API Settings';
		$data['submitname'] = 'Save API Settings';
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		//validation rules
		$this->form_validation->set_rules('hrurl', 'Highrise URL', 'required');
		$this->form_validation->set_rules('hrtoken', 'Highrise API Token', 'required');
	
		if ($this->form_validation->run() == FALSE){
			$this->load->view('settings/settings_view', $data);
		}else{
			
			$this->load->model('Settings_model', 'settings');
			if ($_POST['submit']) {
				//add settings to database
				$this->settings->update_hr_settings();
			}
			//validate highrise settings by pinging highrise api
			$settings = $this->_get_settings();
			$validate = $this->_validate_hr_settings($settings);
			if ($validate != 'valid') {return;}
			//on success goto sync
			redirect('sync/index');
			return;
		}
	}

	/**
	 * Public freshbooks_oauth() method of Settings Controller
	 *
	 * Creates FreshBooks OAuth authorization url
	 * Redirects to FreshBooks authorization url
	 *
	 *
	*/
	public function freshbooks_oauth()
	{
		//get request token - set url for authorize
		try {
			//load freshbooks oauth library
			$settings = $this->_get_settings();
			$this->load->library('FreshbooksOauth', $settings);
			$auth_data = $this->freshbooksoauth->create_authorize_url();
			redirect($auth_data['url']);
			return;
		} catch (Exception $e) {
			if ($e->getMessage() == '302') {
				$data['title'] = 'FreshBooks + Highrise Sync Tool::Register';
				$data['error'] = 'Could not connect to FreshBooks with the given url.  Please check your FreshBooks URL and try again.';
				$this->load->view('user/register_view', $data);
			}else{
				$this->session->set_flashdata('error', $e->getMessage());
				redirect('settings/auth_error');
				return;
			}
		}
	}

	/**
	 * Public request_token_ready() method of Settings Controller
	 *
	 * Extracts verifier and token from FreshBooks Oauth
	 * Requests access tokens from FreshBooks OAuth
	 * Inserts OAuth credentials on register Updates on password reset
	 * Inserts user info on register
	*/
	public function request_token_ready()
	{
		parse_str($_SERVER['QUERY_STRING'] ,$_GET); 
	
		try {
			//load freshbooks oauth library
			$settings = $this->_get_settings();
			$this->load->library('FreshbooksOauth', $settings);
			//request access token
			$verifier = $_GET['oauth_verifier'];
			$token = $_GET['oauth_token'];
			$oauth_settings = array('verifier' => $verifier, 'token' => $token);
			//request access token
			$access_token = $this->freshbooksoauth->obtain_access_token($oauth_settings);
			$this->load->model('Settings_model','settings');
			if ($settings['fb_settings'] || $settings['hr_settings']) {
				$update_settings = $this->settings->update_fb_settings($access_token);
			}else{
				//insert new user and settings
				$this->load->model('User_model', 'user');
				try {
					$insert_user_id = $this->user->insert_user();
					//reset session vars
					$remove = array('password' => '', 'register' => '');
					$this->session->unset_userdata($remove);
					$userinfo = array('userid' => $insert_user_id , 'loggedin' => TRUE, 'hrssl' => 'yes');
					$this->session->set_userdata($userinfo); 
					//insert settings
					$insert_settings = $this->settings->insert_fb_settings($access_token);
				} catch (Exception $e) {
					$data['error'] = $e->getMessage();
					$data['title'] = 'FreshBooks + Highrise Sync Tool::Register';
					$this->load->view('user/register_view', $data);
					return;
				}
			}
		} catch (Exception $e) {
			$this->session->set_flashdata('error', $e->getMessage());
			redirect('settings/auth_error');
			return;
		}
		
		//check for password reset
		$reset = $this->session->userdata('reset_password');
		if ($reset) {
			$remove_session_vars = array('userid' => '', 'reset_password' => '', 'subdomain' => '', 'new_pw' => '');
			//get settings
			try {
				$settings = $this->_get_settings();
				$this->load->library('FreshbooksOauth', $settings);
				$validate = $this->_validate_fb_settings($settings);
				if ($validate != 'valid') {
					//remove reset password session variables - if not valid reload password reset page with error
					$this->session->unset_userdata($remove_session_vars);
					$this->session->set_flashdata('error', 'Unable to reset password.  Unable to verify FreshBooks account authorization.');
					redirect('user/reset_password');
					return;
				}
			} catch (Exception $e) {
				//remove reset password session variables
				$this->session->unset_userdata($remove_session_vars);
				$this->session->set_flashdata('error', $e->getMessage());
				redirect('user/reset_password');
				return;
			}
			//reset password/remove session vars/redirect to login page with message
			try {
				$this->load->model('User_model', 'user');
				$update_pw = $this->user->update_password();
				$this->session->unset_userdata($remove_session_vars);
				$this->session->set_flashdata('message', 'Your password was successfully reset.  Please login to continue.');
				redirect('user/index');
				return;
				
			} catch (Exception $e) {
				//remove reset password session variables
				$this->session->unset_userdata($remove_session_vars);
				$this->session->set_flashdata('error', $e->getMessage());
				redirect('user/reset_password');
			}
		}
		
		//redirect to settings index
		redirect('settings/index');
		return;
	}

	/**
	 * Public auth_eror() method of Settings Controller
	 *
	 * Sets OAuth error and loads error view with message
	*/
	public function auth_error()
	{
		$data['title'] = 'Highrise to FreshBooks Sync :: Oauth Error';
		$data['error'] = $this->session->flashdata('error');
		$this->load->view('settings/oauth_error_view', $data);
		return;
	}

	/**
	 * Checks user login status.
	 *
	 * @return bool	True on success, False and redirect to login on fail
	*/
	private function _check_login()
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
	private function _get_settings()
	{
		//if new registration don't attempt to get settings
		if ($this->session->userdata('register')) {
			$api_settings = FALSE;
		}else{
			$this->load->model('Settings_model','settings');
			$api_settings = $this->settings->get_settings();
		}
		
		$fb_url = 'https://'.$this->session->userdata('subdomain').'.freshbooks.com';
		
		if ($api_settings) {
			$fb_settings = ($api_settings->fb_oauth_token_secret == '' || $api_settings->fb_oauth_token == '') ? FALSE : TRUE;
			$hr_settings = ($api_settings->hrurl == '' || $api_settings->hrtoken == '') ? FALSE : TRUE;
			if ($hr_settings) {
				$hr_url_prefix = ($this->session->userdata('hrssl') == 'yes') ? 'https://' : 'http://';
				$hr_url = $hr_url_prefix.$api_settings->hrurl.'.highrisehq.com';
			}else{
				$hr_url = '';
			}
			
			return array(
				'fb_settings' => $fb_settings,
				'hr_settings' => $hr_settings,
				'fb_url' => $fb_url,
				'fb_oauth_token_secret' => $api_settings->fb_oauth_token_secret,
				'fb_oauth_token' => $api_settings->fb_oauth_token,
				'hrurl' => $hr_url,
				'hrtoken' => $api_settings->hrtoken,
				);
		}else{
			return array(
				'fb_settings' => FALSE,
				'hr_settings' => FALSE,
				'fb_url' => $fb_url,
				'fb_oauth_token_secret' => '',
				'fb_oauth_token' => '',
				'hrurl' => '',
				'hrtoken' => '',
				);
		}
	}
	
	/**
	 * Private _validate_hr_settings() method of Settings Controller
	 *
	 * Checks for Highrise settings in database.  If present then it tests that the
	 * settings are valid by using the Highrise API.
	 *
	 * @return bool true  returns true on success and redirects to Highrise settings page on false
	 *
	*/
	private function _validate_hr_settings($settings)
	{
		$this->load->library('Highrise', $settings);
		try {
			$validate_hr_settings = $this->highrise->validate_hr_settings();
			if ($validate_hr_settings == 'switchssl') {
				//Flip the ssl switch
				$hr_ssl_status = ($this->session->userdata('hrssl') == 'yes') ? 'no' : 'yes';
				$this->session->unset_userdata('hrssl');
				$this->session->set_userdata('hrssl', $hr_ssl_status);
			}
			return 'valid';
		} catch (Exception $e) {
			//if hr settings fail to validate return with message
			$data['title']   = 'Highrise to Freshbooks Sync Tool :: API Settings';
			$data['navigation'] = TRUE;
			$raw_domain = $settings['hrurl'];
			$data['hrurl'] = preg_replace('%http[a-z]*://|\.[a-zA-Z0-9]*\.com%', '', $raw_domain);
			$data['hrtoken'] = $settings['hrtoken'];
			$data['submitname'] = 'Update API Settings';
			$data['error'] = $e->getMessage();
			$this->load->view('settings/settings_view', $data); 
			return;
		}
	}
	
	/**
	 * Private _validate_fb_settings() method of Settings Controller
	 *
	 * Checks for FreshBooks oauth settings in database.  If present then it tests that the
	 * settings are valid by using the FreshBooks API.
	 *
	 * @return bool true  returns true on success and redirects to FreshBooks settings page on false
	 *
	*/	
	private function _validate_fb_settings($settings)
	{
		$this->load->library('FreshbooksOauth', $settings);
		try {
			$validate_fb_settings = $this->freshbooksoauth->validate_fb_settings();
			return 'valid';
		} catch (Exception $e) {
			//if settings no longer valid and freshbooks is not down for maintenance then start oauth process 
			$error = $e->getMessage();
			//if down for maintenance redirect to oauth error page with message
			if ($error == 525) {
				$this->session->set_flashdata('error', 'FreshBooks is currently down for maintenance, please try again later.');
				redirect('settings/auth_error');
				return;
			}
			//start the oauth process
			redirect('settings/freshbooks_oauth');
			return;
		}
	}
}