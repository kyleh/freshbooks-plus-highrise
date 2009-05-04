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
    }
	
	function index()
	{
		//load page specific variables
		$loggedin = $this->session->userdata('loggedin');
		$data['title'] = 'Highrise to Freshbooks Sync Tool::Login';
		$data['heading'] = 'FreshBooks + Highrise Login';
		$data['navigation'] = FALSE;
		//check to see if user is logged in
		if (!$loggedin) {
		$this->load->view('user/login_view',$data);
		}else{
			redirect('settings/index');
		}
	}

	//register new users
	function register()
	{
		//check to see if user is logged in
		$loggedin = $this->session->userdata('loggedin');
		if ($loggedin) {
			redirect('settings/index');
		}
		
		$data['title'] = 'Highrise to Freshbooks Sync Tool::Register for a New Account';
		$data['heading'] = 'Sign Up For A New Account';
		$data['navigation'] = FALSE;
		
		//load form validation helper
		$this->load->library('form_validation');
		$this->form_validation->set_error_delimiters('<p class="error">', '</p>');
		
		$this->form_validation->set_rules('name', 'Full Name', 'required');
		$this->form_validation->set_rules('email', 'Email Address', 'required|valid_email|callback_email_check');
		$this->form_validation->set_rules('password', 'Password', 'required|matches[passconf]');
		$this->form_validation->set_rules('passconf', 'Password Conformation', 'required');

		if ($this->form_validation->run() == FALSE){
			$this->load->view('user/register_view', $data);
		}else{
			$this->load->model('User_model', 'user');
			//insert user
			$this->user->insert_user();
			$user = $this->user->getuser($this->input->post('email'));
			//set up session ans set session vars
			$userinfo = array('userid' => $user[0]->id, 'loggedin' => TRUE, 'username' => $user[0]->email);
			$this->session->set_userdata($userinfo); 
			redirect('settings/index');
		}
	}

	//email callbback form validation function
	function email_check($str)
	{
		$this->load->model('User_model', 'user');
		$email_in_db = $this->user->check_for_email($str);

		if ($email_in_db == TRUE) {
			$this->form_validation->set_message('email_check', 'The %s is already in use please use another email address.');
			return FALSE;
		}else{
			return TRUE;
		}
	}
	
	//verify valid user
	function verify()
	{
		$data['title'] = 'Highrise to Freshbooks Sync Tool::Login';
		$data['heading'] = 'FBsync Login';
		$data['navigation'] = FALSE;
		
		$this->load->model('User_model', 'user');
		$user = $this->user->getuser($this->input->post('email'));
		$password = md5($this->input->post('password'));

		if ($user) {
			if ($user[0]->password == $password) {
				//start session - set vars
				$userinfo = array('userid' => $user[0]->id, 'loggedin' => TRUE, 'username' => $user[0]->email);
				$this->session->set_userdata($userinfo);
				//check for settings
				$this->load->model('Settings_model', 'settings');
				$got_settings = $this->settings->got_settings();
				if ($got_settings > 0) {
					redirect('sync/index');
				}else{
					redirect('settings/index');
				}
			}else{
				//return with error message
				$data['error'] = "Invalid Email or Password - Please Try Again.";
			}
		}else{
			//return with error message
			$data['error'] = "Your Email Address Was Not Found";
		}
		$this->load->view('user/login_view',$data);

	}
	
	function logout()
	{
		$this->session->sess_destroy();
		redirect('user/index');
	}

}
?>