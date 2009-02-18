<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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
		$data['error'] = FALSE;
		$data['navigation'] = FALSE;
		//check to see if user is logged in
		if (!$loggedin) {
		$this->load->view('user/login_view',$data);
		}else{
			redirect('settings/index');
		}
	}

	function register()
	{
		//check to see if user is logged in
		$loggedin = $this->session->userdata('loggedin');
		if ($loggedin) {
			redirect('settings/index');
		}
		
		$data['title'] = 'Highrise to Freshbooks Sync Tool::Register for a New Account';
		$data['heading'] = 'Sign Up For A New Account';
		$data['error'] = '';
		$data['navigation'] = False;
		//form validation		
		$this->load->library('validation');
		$this->validation->set_error_delimiters('<div class="error">', '</div>');

		$rules['name']		= "required";
		$rules['email']		= "required|valid_email|callback_email_check";
		$rules['password']	= "required|matches[passconf]";
		$rules['passconf']	= "required";

		$this->validation->set_rules($rules);

		$fields['name']		= 'Full Name';
		$fields['password']	= 'Password';
		$fields['passconf']	= 'Password Confirmation';
		$fields['email']	= 'Email Address';

		$this->validation->set_fields($fields);

		if ($this->validation->run() == FALSE){
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

	function email_check($str)
	{
		$this->load->model('User_model', 'email');
		$mail_db = $this->email->check_for_email($str);

		if ($mail_db > 0) {
			$this->validation->set_message('email_check', 'The %s field  is already in use please use another email address.');
			return FALSE;
		}else{
			return TRUE;
		}
	}

	function verify()
	{
		$data['title'] = 'Highrise to Freshbooks Sync Tool::Login';
		$data['heading'] = 'FBsync Login';
		$data['error'] = FALSE;
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
		$this->load->view('login_view',$data);

	}
	
	function logout()
	{
		$this->session->sess_destroy();
		redirect('user/index');
	}

}
?>