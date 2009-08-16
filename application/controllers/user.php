<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
*User Controller
*Controller to login verifications and registration
*Created by Kyle Hendricks - Mend Technologies - kyleh@mendtechnologies.com
*Ver. 1.0 5/3/2009
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

Class User extends Controller {
	
	function __construct()
  {
   parent::Controller();
	$this->load->helper(array('form', 'url', 'html'));
	$this->load->model('User_model', 'user');
  }
	
	function index()
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
			//check oauth settings
			redirect('oa_settings/index');
		}
	}
		
	private function _verify_fb_settings()
	{
		$this->load->model('Oa_settings_model','settings');
		$settings = $this->settings->get_settings();
				
		if ($settings == false || $settings->fb_oauth_token_secret == '')
		{
			redirect('oa_settings/freshbooks_oauth');
			return;
		}else{
			$fb_url = 'https://'.$this->session->userdata('subdomain').'.freshbooks.com';
			$settings = array(
									'fb_url' => $fb_url,
									'fb_oauth_token_secret' => $settings->fb_oauth_token_secret,
									'fb_oauth_token' => $settings->fb_oauth_token,
									'hrurl' => $settings->hrurl,
									'hrtoken' => $settings->hrtoken,
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
	
	// private function _verify_hr_settings()
	// {
	// 	
	// 	
	// }

	private function _register_user()
	{
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<span class="login_error">', '</span>');
		
		$this->form_validation->set_rules('fburl', 'FreshBooks Url', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|sha1');
		
		if ($this->form_validation->run() == FALSE){
			$data['title'] = 'FreshBooks + Highrise Sync Tool::Login';
			$this->load->view('user/login_view', $data);
		}else{
			//check for .freshbooks.com or https:// or http:// and remove if present
			$fb_url = $this->input->post('fburl');
			$remove = array('.freshbooks.com', 'http://', 'https://');
			$fb_url = str_replace($remove, '', $fb_url);
			
			//insert user
			$insert_user = $this->user->insert_user($fb_url);
			//get user by FreshBooks Url
			$user = $this->user->get_user($fb_url);
			//set up session ans set session vars
			$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'subdomain' => $user->fb_url);
			$this->session->set_userdata($userinfo); 
			redirect('oa_settings/freshbooks_oauth');
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





	//email callbback form validation function
	// function email_check($str)
	// {
	// 	$this->load->model('User_model', 'user');
	// 	$email_in_db = $this->user->check_for_email($str);
	// 
	// 	if ($email_in_db == TRUE) {
	// 		$this->form_validation->set_message('email_check', 'The %s is already in use please use another email address.');
	// 		return FALSE;
	// 	}else{
	// 		return TRUE;
	// 	}
	// }
	
	//verify valid user
	function verify()
	{
		//check for .freshbooks.com or https:// or http:// and remove if present
		$fb_url = $this->input->post('fburl', TRUE);
		$remove = array('.freshbooks.com', 'http://', 'https://');
		$fb_url = str_replace($remove, '', $fb_url);
		//check database for freshbooks subdomain
		$user = $this->user->get_user($fb_url);
		
		
		// $data['debug'] = $user;
		// $this->load->view('test_view', $data);
		// return;
		
				
		if ($user) {
			$password = sha1($this->input->post('password', TRUE));
			//set session vars
			if ($user->password == $password) {
				//set session data
				$userinfo = array('userid' => $user->id, 'loggedin' => TRUE, 'subdomain' => $user->fb_url);
				$this->session->set_userdata($userinfo); 
				
								
				
				//check for fb settings
				$fb_settings = $this->_verify_fb_settings();
	
				//check highrise settings
				//$hr_settings = $this->_verify_highrise_settings();
				
				
			
			}else{
				$data['error'] = "Invalid Email or Password - Please Try Again.";
				$this->load->view('user/login_view',$data);
			}
		}else{
			$register = $this->_register_user();
		}
		
		
		
		// if ($user) {
		// 	if ($user[0]->password == $password) {
		// 		//start session - set vars
		// 		$userinfo = array('userid' => $user[0]->id, 'loggedin' => TRUE, 'username' => $user[0]->email);
		// 		$this->session->set_userdata($userinfo);
		// 		//check for settings
		// 		$this->load->model('Oa_settings_model', 'settings');
		// 		$got_settings = $this->settings->got_settings();
		// 		if ($got_settings > 0) {
		// 			redirect('sync/index');
		// 		}else{
		// 			redirect('oa_settings/index');
		// 		}
		// 	}else{
		// 		//return with error message
		// 		$data['error'] = "Invalid Email or Password - Please Try Again.";
		// 	}
		// }else{
		// 	//return with error message
		// 	$data['error'] = "Your Email Address Was Not Found";
		// }
		// $this->load->view('user/login_view',$data);
	
	}
	
	function logout()
	{
		$this->session->sess_destroy();
		redirect('user/index');
	}

}
?>