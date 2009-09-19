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
		//check to see if user is logged in
		if (!$loggedin) {
			$this->load->view('user/login_view',$data);
		}else{
			//TODO: check oauth settings
			redirect('oa_settings/index');
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
		$remove = array('.freshbooks.com', 'http://', 'https://');
		$fb_url = str_replace($remove, '', $fb_url);
		//check database for freshbooks subdomain
		$user = $this->user->get_user_by_url($fb_url);
		if ($user) {
			$password = sha1($this->input->post('password', TRUE));
			//set session vars
			if ($user->password == $password) {
				//set session data
				$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'subdomain' => $user->fb_url);
				$this->session->set_userdata($userinfo); 
				//check for fb settings
				$fb_settings = $this->_verify_fb_settings();
				if ($fb_settings) {
					//check highrise settings
					$hr_settings = $this->_verify_highrise_settings();
					//redirect to sync
					redirect('sync/index');
				}
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
			$reset_password = $this->_reset_password();
			return;
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
	 * Private _verify_fb_settings() method of User Controller
	 *
	 * Checks for FreshBooks oauth settings in database.  If present then it tests that the
	 * settings are valid by using the FreshBooks API.
	 *
	 * @return bool true  returns true on success and redirects to FreshBooks settings page on false
	 *
	*/
	private function _verify_fb_settings()
	{
		$this->load->model('Oa_settings_model','settings');
		$settings = $this->settings->get_settings();
				
		if ($settings == false || $settings->fb_oauth_token_secret == ''){
			redirect('oa_settings/freshbooks_oauth');
			return;
		} else {
			$fb_url = 'https://'.$this->session->userdata('subdomain').'.freshbooks.com';
			$settings = array(
				'fb_url' 				=> $fb_url,
				'fb_oauth_token_secret' => $settings->fb_oauth_token_secret,
				'fb_oauth_token' 		=> $settings->fb_oauth_token,
				'hrurl' 				=> $settings->hrurl,
				'hrtoken' 				=> $settings->hrtoken,
				);
			
			$this->load->library('FreshbooksOauth', $settings);
			try {
				$fb_test = $this->freshbooksoauth->test_fb_settings();
				return true;
			} catch (Exception $e) {
				redirect('oa_settings/freshbooks_oauth');
				return;
			}
		}
	}
	
	/**
	 * Private _verify_hr_settings() method of User Controller
	 *
	 * Checks for Highrise settings in database.  If present then it tests that the
	 * settings are valid by using the Highrise API.
	 *
	 * @return bool true  returns true on success and redirects to Highrise settings page on false
	 *
	*/
	private function _verify_hr_settings()
	{
		//
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
			$remove = array('.freshbooks.com', 'http://', 'https://');
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
			$userinfo = array('userid' => $insert_user_id , 'loggedin' => TRUE, 'subdomain' => $fb_url);
			$this->session->set_userdata($userinfo); 
			redirect('oa_settings/freshbooks_oauth');
			return;
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
	
	//register new users
	// function register()
	// {
	// 	//check to see if user is logged in
	// 	$loggedin = $this->session->userdata('loggedin');
	// 	if ($loggedin) {
	// 		redirect('oa_settings/index');
	// 	}
	// 	
	// 	$data['title'] = 'Highrise to Freshbooks Sync Tool::Register for a New Account';
	// 	$data['heading'] = 'Sign Up For A New Account';
	// 	$data['navigation'] = FALSE;
	// 	
	// 	//load form validation helper
	// 	$this->load->library('form_validation');
	// 	$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
	// 	
	// 	$this->form_validation->set_rules('name', 'Full Name', 'required');
	// 	$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|callback_email_check');
	// 	$this->form_validation->set_rules('password', 'Password', 'required|matches[passconf]');
	// 	$this->form_validation->set_rules('passconf', 'Password Conformation', 'required');
	// 
	// 	if ($this->form_validation->run() == FALSE){
	// 		$this->load->view('user/register_view', $data);
	// 	}else{
	// 		$this->load->model('User_model', 'user');
	// 		//insert user
	// 		$this->user->insert_user();
	// 		$user = $this->user->getuser($this->input->post('email'));
	// 		//set up session ans set session vars
	// 		$userinfo = array('userid' => $user[0]->id, 'loggedin' => TRUE, 'username' => $user[0]->email);
	// 		$this->session->set_userdata($userinfo); 
	// 		redirect('oa_settings/index');
	// 	}
	// }






}
?>