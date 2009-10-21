<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * User Controller
 *
 * Controller to login verifications and registration
 *
 * @author Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
 * @version 1.0 - August 2009
 *
 * @copyright 2009 - Kyle Hendricks - Mend Technologies
 *
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 *
 * Neither the name of the <ORGANIZATION> nor the names of its
 * contributors may be used to endorse or promote products derived from this
 * software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

Class User extends Controller {
	
	function __construct()
  	{
		parent::Controller();
		$this->load->helper(array('form', 'url', 'html'));
		$this->load->model('User_model', 'user');
  	}
	
	/**
	 * Index action of User Controller (default)
	 *
	 * If user is not logged in sent to login page.  If user is logged in check settings
	 * and redirect to settings or sync page
	 *
	*/
	public function index()
	{
		//load page specific variables
		$loggedin = $this->session->userdata('loggedin');
		$data['title'] = 'FreshBooks + Highrise Sync Tool::Login';
		$data['heading'] = 'FreshBooks + Highrise Login';
		$data['navigation'] = FALSE;
		if ($this->session->flashdata('message')) {
			$data['message'] = $this->session->flashdata('message');
		}
		//check to see if user is logged in
		if (!$loggedin) {
			$this->load->view('user/login_view',$data);
		}else{
			redirect('settings/index');
			return;
		}
	}
	
	/**
	 * Public verify() method of User Controller
	 *
	 * Attempts to lookup user by FreshBooks url.  If user exists, check password then
	 * verify FreshBooks and Highrise settings.
	 *
	 * @param array|string  $_POST['fburl']  FreshBooks Url
	 * @param array|string  $_POST['password]  Account Password
	 *
	*/
	public function verify()
	{
		$data['title'] = 'FreshBooks + Highrise Sync Tool::Login';
		//check for .freshbooks.com or https:// or http:// and remove if present
		$fb_url = $this->input->post('fburl', TRUE);
		$remove = array('freshbooks', 'com', '/', 'http', 'https', ':', '.');
		$fb_url = str_replace($remove, '', $fb_url);
		//check database for freshbooks subdomain
		$user = $this->user->get_user_by_url($fb_url);
		if ($user) {
			$password = sha1($this->input->post('password', TRUE));
			//set session vars
			if ($user->password == $password) {
				//set session data
				$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'subdomain' => $user->fb_url, 'hrssl' => 'no');
				$this->session->set_userdata($userinfo); 
				
				//check for fb settings
				$settings = $this->_get_settings();
				if ($settings['fb_settings']) {
					//validate fb settings
					$validate = $this->_validate_fb_settings($settings);
					
				}else{
					redirect('settings/index');
				}
				//check hr settings
				if ($settings['hr_settings']) {
					//validate fb settings
					$validate = $this->_validate_hr_settings($settings);
				}else{
					redirect('settings/index');
				}
				//if settings validate redirect to sync
				redirect('sync/index');
			} else {
				$data['error'] = "Invalid Password - Please Try Again.";
				$data['fb_url'] = $fb_url;
				$this->load->view('user/login_view',$data);
			}
		} else {
			$data['error'] = "FreshBooks url is not currently registered - Please click the - Create an account - link on the right to register.";
			$this->load->view('user/login_view', $data);
		}
	}
	
	/**
	 * Public verify() method of User Controller
	 *
	 * Registers new users using Post form data via the private method _register_users()
	 * 
	 * @param array|string  $_POST['register']  trigger that form data exists
	 *
	*/
	public function register()
	{
		$data['title'] = 'FreshBooks + Highrise Sync Tool::Register';
		$data['heading'] = 'FreshBooks + Highrise Register';
		
		//check for post data
		if ($this->input->post('register')) {
			$create_account = $this->_register_user();
			return;
		}
		//display registration view
		$this->load->view('user/register_view',$data);
	}
	
	/**
	 * Public change_password() method of User Controller
	 *
	 * Allow user to change password of an already existing account
	 * 
	*/
	public function reset_password()
	{
		$data['title'] = 'FreshBooks + Highrise Sync Tool::Reset Password';
		$data['heading'] = 'FreshBooks + Highrise Reset Password';
		
		//check for post data
		if ($this->input->post('reset_password')) {
			$reset_password = $this->_create_new_password();
			return;
		}elseif($this->session->flashdata('error')){
			$data['error'] = $this->session->flashdata('error');
		}
		//display registration view
		$this->load->view('user/reset_password_view',$data);
	}
	
	/**
	 * Public logout() method of User Controller
	 *
	 * Logs out user and destroys session data
	 * 
	*/
	function logout()
	{
		$this->session->sess_destroy();
		redirect('user/index');
	}
	
	/**
	 * Private _validate_hr_settings() method of User Controller
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
		// return 'TEST ME';
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
	 * Private _validate_fb_settings() method of User Controller
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
			return TRUE;
		} catch (Exception $e) {
			//if settings no longer valid and freshbooks is not down for maintenance then start oauth process 
			$error = $e->getMessage();
			//if down for maintenance redirect to oauth error page with message
			if ($error == 503) {
				$data['error'] = 'FreshBooks is currently down for maintenance, please try again later.';
				$data['title'] = 'Highrise to FreshBooks Sync :: Oauth Error';
				$this->load->view('settings/oauth_error_view', $data);
				return;
			}
			//start the oauth process
			redirect('settings/index');
			return;
		}
	}
	
	/**
	 * Private _register_user() method of User Controller
	 *
	 * Validates user input via Post. Checks that the FreshBooks Url is not already in use.  Add user to database
	 * on success then sets user session variables and redirects to FreshBooks settings page.
	 *
	 * @param  array|string  $_POST['fburl]  FreshBooks subdomain
	 * @param  array|string  $_POST['password']  User password
	 * @param  array|string  $_POST['confirmpassword']  User password confirmation
	 *
	*/
	private function _register_user()
	{
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="login_error">', '</span>');
		
		$this->form_validation->set_rules('fburl', 'FreshBooks Url', 'trim|required|callback_fb_url_check');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|sha1');
		
		if ($this->form_validation->run() == FALSE){
			$data['title'] = 'FreshBooks + Highrise Sync Tool::Register';
			$this->load->view('user/register_view', $data);
		}else{
			//check for .freshbooks.com or https:// or http:// and remove if present
			$fb_url = $this->input->post('fburl');
			$remove = array('freshbooks', 'com', '/', 'http', 'https', ':', '.');
			$fb_url = str_replace($remove, '', $fb_url);
			//insert user
			$insert_user_id = $this->user->insert_user($fb_url);
			//if insert fails return to registration page with error
			if ($insert_user_id == FALSE) {
				$data['error'] = 'Unable to add User data to database.  Please try again.';
				$data['title'] = 'FreshBooks + Highrise Sync Tool::Register';
				$this->load->view('user/register_view', $data);
				return;
			}
			//set up session and set session vars
			$userinfo = array('userid' => $insert_user_id , 'loggedin' => TRUE, 'subdomain' => $fb_url, 'hrssl' => 'yes');
			$this->session->set_userdata($userinfo); 
			redirect('settings/index');
			return;
		}
	}
	
	private function _create_new_password()
	{
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="login_error">', '</span>');
		
		$this->form_validation->set_rules('fburl', 'FreshBooks Url', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|matches[confpassword]|sha1');
		$this->form_validation->set_rules('confpassword', 'Confirm Password', 'trim|required');
		
		if ($this->form_validation->run() == FALSE){
			$data['title'] = 'FreshBooks + Highrise Sync Tool::Reset Password';
			$this->load->view('user/reset_password_view', $data);
		}else{
			//check for .freshbooks.com or https:// or http:// and remove if present
			$fb_url = $this->input->post('fburl', TRUE);
			$remove = array('freshbooks', 'com', '/', 'http', 'https', ':', '.');
			$fb_url = str_replace($remove, '', $fb_url);
			$user = $this->user->get_user_by_url($fb_url);
			if ($user) {
				//add new password data to session
				$resetdata = array('userid' => $user->id, 'reset_password' => 'true', 'subdomain' => $user->fb_url, 'new_pw' => $this->input->post('password'));
				$this->session->set_userdata($resetdata);
				//redirect to oauth process
				redirect('settings/freshbooks_oauth'); 
			}
		}
	}
	
	
	/**
	 * Callback method fb_url_check() method of User Controller
	 *
	 * Callback method used in input validation of _register_user() method.  Checks to see if the
	 * user supplied FreshBooks Url is already registered to an existing user.
	 *
	 * @param  string  $str  Value of Post parameter using the callback function.
	 *
	*/
	function fb_url_check($str)
	{
		$this->load->model('User_model', 'user');
		$fb_url_in_db = $this->user->get_user_by_url($str);
	
		if ($fb_url_in_db == TRUE) {
			$this->form_validation->set_message('fb_url_check', 'The %s is already in use please use another FreshBooks Url or return to the login page and reset your password.');
			return FALSE;
		}else{
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
		$this->load->model('Oa_settings_model','settings');
		$api_settings = $this->settings->get_settings();
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
}
?>